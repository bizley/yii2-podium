<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\models;

use bizley\podium\components\Cache;
use bizley\podium\components\Helper;
use bizley\podium\db\UserQuery;
use bizley\podium\log\Log;
use bizley\podium\Module as Podium;
use bizley\podium\rbac\Rbac;
use Exception;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\Json;
use yii\web\IdentityInterface;
use Zelenin\yii\behaviors\Slug;
use Zelenin\yii\widgets\Recaptcha\validators\RecaptchaValidator;

/**
 * User model
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 * @property integer $id
 * @property integer $inherited_id
 * @property string $username
 * @property string $slug
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $activation_token
 * @property string $email
 * @property string $new_email
 * @property string $auth_key
 * @property integer $status
 * @property integer $role
 * @property integer $anonymous
 * @property string $timezone
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $current_password write-only password
 * @property string $password write-only password
 * @property string $password_repeat write-only password repeated
 * @property integer $tos write-only terms of service agreement
 */
class User extends ActiveRecord implements IdentityInterface
{

    /**
     * Statuses.
     */
    const STATUS_REGISTERED = 1;
    const STATUS_BANNED     = 9;
    const STATUS_ACTIVE     = 10;
    
    /**
     * Roles.
     */
    const ROLE_MEMBER       = 1;
    const ROLE_MODERATOR    = 9;
    const ROLE_ADMIN        = 10;
    
    const DEFAULT_TIMEZONE = 'UTC';
    
    /**
     * @var string Captcha.
     */
    public $captcha;
    
    /**
     * @var string Current password for profile update (write-only).
     */
    public $current_password;
    
    /**
     * @var string Unencrypted password (write-only).
     */
    public $password;
    
    /**
     * @var string Unencrypted password for change (write-only).
     */
    public $new_password;
    
    /**
     * @var string Unencrypted password repeated (write-only).
     */
    public $password_repeat;
    
    /**
     * @var string Unencrypted password repeated for change (write-only).
     */
    public $new_password_repeat;
    
    /**
     * @var int Terms of service agreement flag (write-only).
     */
    public $tos;
    
    private $_access = [];
    private static $_identity;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%podium_user}}';
    }
    
    /**
     * @inheritdoc
     */
    public static function find()
    {
        return new UserQuery(get_called_class());
    }
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            [
                'class'     => Slug::className(),
                'attribute' => 'username',
                'immutable' => false,
            ],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = [
            'installation'   => [],
            'token'          => [],
            'ban'            => [],
            'role'           => [],
            'passwordChange' => ['password', 'password_repeat'],
            'register'       => ['email', 'password', 'password_repeat'],
            'account'        => ['username', 'anonymous', 'new_email', 'new_password', 'new_password_repeat', 'timezone', 'current_password'],
            'accountInherit' => ['username', 'anonymous', 'new_email', 'timezone', 'current_password'],
        ];
        
        if (Podium::getInstance()->config->get('use_captcha')) {
            $scenarios['register'][] = 'captcha';
        }
        
        return $scenarios;
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [['email', 'password', 'password_repeat', 'tos'], 'required', 'except' => ['account']],
            ['current_password', 'required'],
            ['current_password', 'validateCurrentPassword'],
            [['email', 'new_email'], 'email', 'message' => Yii::t('podium/view', 'This is not a valid e-mail address.')],
            [['email', 'new_email'], 'string', 'max' => 255, 'message' => Yii::t('podium/view', 'Provided e-mail address is too long.')],
            ['email', 'unique'],
            ['new_email', 'unique', 'targetAttribute' => 'email'],
            [['password', 'new_password'], 'passwordRequirements'],
            ['password_repeat', 'compare', 'compareAttribute' => 'password'],
            ['new_password_repeat', 'compare', 'compareAttribute' => 'new_password'],
            ['username', 'unique'],
            ['username', 'validateUsername'],
            ['anonymous', 'boolean'],
            ['inherited_id', 'integer'],
            ['timezone', 'match', 'pattern' => '/[\w\-]+/'],
            ['status', 'default', 'value' => self::STATUS_REGISTERED],
            ['role', 'default', 'value' => self::ROLE_MEMBER],
            ['tos', 'in', 'range' => [1], 'message' => Yii::t('podium/view', 'You have to read and agree on ToS.')],
        ];
        
        if (Podium::getInstance()->config->get('recaptcha_sitekey') !== '' && Podium::getInstance()->config->get('recaptcha_secretkey') !== '') {
            $rules[] = ['captcha', RecaptchaValidator::className(), 'secret' => Podium::getInstance()->config->get('recaptcha_secretkey')];
        }
        else {
            $rules[] = ['captcha', 'captcha', 'captchaAction' => 'podium/account/captcha'];
        }
        
        return $rules;
    }
    
    /**
     * Activates account.
     * @return boolean
     */
    public function activate()
    {
        if ($this->status == self::STATUS_REGISTERED) {
            $this->removeActivationToken();
            $this->status = self::STATUS_ACTIVE;

            $transaction = static::getDb()->beginTransaction();
            try {
                if ($this->save()) {
                    if (Yii::$app->authManager->assign(Yii::$app->authManager->getRole(Rbac::ROLE_USER), $this->id)) {
                        $transaction->commit();
                        return true;
                    }
                }
            }
            catch (Exception $e) {
                $transaction->rollBack();
                Log::error($e->getMessage(), null, __METHOD__);
            }
        }

        return false;
    }
    
    /**
     * Changes email address.
     * @return boolean
     */
    public function changeEmail()
    {
        $this->email     = $this->new_email;
        $this->new_email = null;
        $this->removeEmailToken();
        return $this->save();
    }
    
    /**
     * Changes password.
     * @return boolean
     */
    public function changePassword()
    {
        $this->setPassword($this->password);
        $this->generateAuthKey();
        $this->removePasswordResetToken();
        return $this->save();
    }
    
    /**
     * Returns current user based on module configuration.
     * @return mixed
     */
    public static function findMe()
    {
        if (Podium::getInstance()->userComponent == Podium::USER_INHERIT) {
            if (static::$_identity === null) {
                static::$_identity = static::find()->where(['inherited_id' => Yii::$app->user->id])->limit(1)->one();
            }
            return static::$_identity;
        }
        return Yii::$app->user->identity;
    }
    
    /**
     * Finds registered user by activation token.
     * @param string $token activation token
     * @return static|null
     */
    public static function findByActivationToken($token)
    {
        if (!static::isActivationTokenValid($token)) {
            return null;
        }
        return static::findOne(['activation_token' => $token, 'status' => self::STATUS_REGISTERED]);
    }
    
    /**
     * Finds active user by email.
     * @param string $email
     * @return static|null
     */
    public static function findByEmail($email)
    {
        return static::find()->where(['email' => $email, 'status' => self::STATUS_ACTIVE])->limit(1)->one();
    }
    
    /**
     * Finds active user by email token.
     * @param string $token activation token
     * @return static|null
     */
    public static function findByEmailToken($token)
    {
        if (!static::isEmailTokenValid($token)) {
            return null;
        }
        return static::find()->where(['email_token' => $token, 'status' => self::STATUS_ACTIVE])->limit(1)->one();
    }
    
    /**
     * Finds user of given status by username or email.
     * @param string $keyfield value to compare
     * @param integer $status
     * @return static|null
     */
    public static function findByKeyfield($keyfield, $status = self::STATUS_ACTIVE)
    {
        if ($status === null) {
            return static::find()->where(['or', ['email' => $keyfield], ['username' => $keyfield]])->limit(1)->one();
        }
        return static::find()->where(['and', ['status' => $status], ['or', ['email' => $keyfield], ['username' => $keyfield]]])->limit(1)->one();
    }
    
    /**
     * Finds user of given status by password reset token
     * @param string $token password reset token
     * @param string|null $status user status or null
     * @return static|null
     */
    public static function findByPasswordResetToken($token, $status = self::STATUS_ACTIVE)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }
        if ($status == null) {
            return static::find()->where(['password_reset_token' => $token])->limit(1)->one();
        }
        return static::find()->where(['password_reset_token' => $token, 'status' => $status])->limit(1)->one();
    }
    
    /**
     * Finds active user by username.
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::find()->where(['username' => $username, 'status' => self::STATUS_ACTIVE])->limit(1)->one();
    }
    
    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::find()->where(['id' => $id, 'status' => self::STATUS_ACTIVE])->limit(1)->one();
    }
    
    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }
    
    /**
     * Generates new activation token.
     */
    public function generateActivationToken()
    {
        $this->activation_token = Yii::$app->security->generateRandomString() . '_' . time();
    }
    
    /**
     * Generates "remember me" authentication key.
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }
    
    /**
     * Generates new email token.
     */
    public function generateEmailToken()
    {
        $this->email_token = Yii::$app->security->generateRandomString() . '_' . time();
    }
    
    /**
     * Generates new password reset token.
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }
    
    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }
    
    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }
    
    /**
     * Activity relation.
     * @return Activity
     */
    public function getActivity()
    {
        return $this->hasOne(Activity::className(), ['user_id' => 'id']);
    }
    
    /**
     * Friends relation.
     * @return ActiveQuery
     * @since 0.2
     */
    public function getFriends()
    {
        return $this->hasMany(User::className(), ['id' => 'friend_id'])->viaTable('{{%podium_user_friend}}', ['user_id' => 'id']);
    }
    
    /**
     * Meta relation.
     * @return ActiveQuery
     */
    public function getMeta()
    {
        return $this->hasOne(Meta::className(), ['user_id' => 'id']);
    }
    
    /**
     * Returns number of new user messages.
     * @return integer
     */
    public function getNewMessagesCount()
    {
        $cache = Podium::getInstance()->cache->getElement('user.newmessages', $this->id);
        if ($cache === false) {
            $cache = (new Query)->from(MessageReceiver::tableName())->where(['receiver_id' => $this->id,
                        'receiver_status' => Message::STATUS_NEW])->count();
            Podium::getInstance()->cache->setElement('user.newmessages', $this->id, $cache);
        }

        return $cache;
    }
    
    /**
     * Returns Podium name.
     * @return string
     */
    public function getPodiumName()
    {
        return $this->username ? $this->username : 'Forum#' . $this->id;
    }
    
    /**
     * Returns Podium name tag.
     * @param boolean $simple
     * @return string
     */
    public function getPodiumTag($simple = false)
    {
        return Helper::podiumUserTag($this->podiumName, $this->role, $this->id, $this->podiumSlug, $simple);
    }
    
    /**
     * Returns Podium member slug.
     * @return string
     */
    public function getPodiumSlug()
    {
        return $this->slug ? $this->slug : 'forum-' . $this->id;
    }
    
    /**
     * Returns number of active posts added by user.
     * @return integer
     */
    public function getPostsCount()
    {
        return static::findPostsCount($this->id);
    }
    
    /**
     * Returns number of active posts added by user of given ID.
     * @param integer $id
     * @return integer
     */
    public static function findPostsCount($id)
    {
        $cache = Podium::getInstance()->cache->getElement('user.postscount', $id);
        if ($cache === false) {
            $cache = (new Query)->from(Post::tableName())->where(['author_id' => $id])->count();
            Podium::getInstance()->cache->setElement('user.postscount', $id, $cache);
        }

        return $cache;
    }
    
    /**
     * Returns number of active threads added by user.
     * @return integer
     */
    public function getThreadsCount()
    {
        return static::findThreadsCount($this->id);
    }
    
    /**
     * Returns number of active threads added by user of given ID.
     * @param integer $id
     * @return integer
     */
    public static function findThreadsCount($id)
    {
        $cache = Podium::getInstance()->cache->getElement('user.threadscount', $id);
        if ($cache === false) {
            $cache = (new Query)->from(Thread::tableName())->where(['author_id' => $id])->count();
            Podium::getInstance()->cache->setElement('user.threadscount', $id, $cache);
        }

        return $cache;
    }
    
    /**
     * Returns list of roles.
     * @return array
     */
    public static function getRoles()
    {
        return [
            self::ROLE_MEMBER    => Yii::t('podium/view', 'Member'),
            self::ROLE_MODERATOR => Yii::t('podium/view', 'Moderator'),
            self::ROLE_ADMIN     => Yii::t('podium/view', 'Admin'),
        ];
    }
    
    /**
     * Returns list of moderators roles.
     * @return array
     */
    public static function getModRoles()
    {
        return [
            self::ROLE_MODERATOR => Yii::t('podium/view', 'Moderator'),
            self::ROLE_ADMIN     => Yii::t('podium/view', 'Admin'),
        ];
    }
    
    /**
     * Returns list of statuses.
     * @return array
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_ACTIVE     => Yii::t('podium/view', 'Active'),
            self::STATUS_BANNED     => Yii::t('podium/view', 'Banned'),
            self::STATUS_REGISTERED => Yii::t('podium/view', 'Registered'),
        ];
    }
    
    /**
     * Returns number of user subscribed threads with new posts.
     * @return integer
     */
    public function getSubscriptionsCount()
    {
        $cache = Podium::getInstance()->cache->getElement('user.subscriptions', $this->id);
        if ($cache === false) {
            $cache = (new Query)->from(Subscription::tableName())->where(['user_id' => $this->id,
                        'post_seen' => Subscription::POST_NEW])->count();
            Podium::getInstance()->cache->setElement('user.subscriptions', $this->id, $cache);
        }

        return $cache;
    }
    
    /**
     * Finds out if activation token is valid.
     * @param string $token activation token
     * @return boolean
     */
    public static function isActivationTokenValid($token)
    {
        $expire = Podium::getInstance()->config->get('activation_token_expire');
        if ($expire === null) {
            $expire = 3 * 24 * 60 * 60;
        }
        return static::isTokenValid($token, $expire);
    }
    
    /**
     * Finds out if email token is valid.
     * @param string $token activation token
     * @return boolean
     */
    public static function isEmailTokenValid($token)
    {
        $expire = Podium::getInstance()->config->get('email_token_expire');
        if ($expire === null) {
            $expire = 24 * 60 * 60;
        }
        return static::isTokenValid($token, $expire);
    }
    
    /**
     * Finds out if password reset token is valid.
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        $expire = Podium::getInstance()->config->get('password_reset_token_expire');
        if ($expire === null) {
            $expire = 24 * 60 * 60;
        }
        return static::isTokenValid($token, $expire);
    }
    
    /**
     * Finds out if given token type is valid.
     * @param string $token activation token
     * @param integer $expire expire time
     * @return boolean
     */
    public static function isTokenValid($token, $expire)
    {
        if (empty($token) || empty($expire)) {
            return false;
        }
        $parts     = explode('_', $token);
        $timestamp = (int)end($parts);
        return $timestamp + (int)$expire >= time();
    }
    
    /**
     * Finds out if user is befriended by another.
     * @param integer $user_id user ID
     * @return boolean
     * @since 0.2
     */
    public function isBefriendedBy($user_id)
    {
        if ((new Query)->select('id')->from('{{%podium_user_friend}}')->where(['user_id' => $user_id, 'friend_id' => $this->id])->exists()) {
            return true;
        }
        return false;
    }
    
    /**
     * Finds out if user is befriending another.
     * @param integer $user_id user ID
     * @return boolean
     * @since 0.2
     */
    public function isFriendOf($user_id)
    {
        if ((new Query)->select('id')->from('{{%podium_user_friend}}')->where(['user_id' => $this->id, 'friend_id' => $user_id])->exists()) {
            return true;
        }
        return false;
    }
    
    /**
     * Finds out if user is ignored by another.
     * @param integer $user_id user ID
     * @return boolean
     */
    public function isIgnoredBy($user_id)
    {
        if ((new Query)->select('id')->from('{{%podium_user_ignore}}')->where(['user_id' => $user_id, 'ignored_id' => $this->id])->exists()) {
            return true;
        }
        return false;
    }
    
    /**
     * Finds out if user is ignoring another.
     * @param integer $user_id user ID
     * @return boolean
     */
    public function isIgnoring($user_id)
    {
        if ((new Query)->select('id')->from('{{%podium_user_ignore}}')->where(['user_id' => $this->id, 'ignored_id' => $user_id])->exists()) {
            return true;
        }
        return false;
    }
    
    /**
     * Finds out if unencrypted password fulfill requirements.
     */
    public function passwordRequirements($attribute, $params)
    {
        if (!preg_match('~\p{Lu}~', $this->$attribute) ||
                !preg_match('~\p{Ll}~', $this->$attribute) ||
                !preg_match('~[0-9]~', $this->$attribute) ||
                mb_strlen($this->$attribute, 'UTF-8') < 6 ||
                mb_strlen($this->$attribute, 'UTF-8') > 100) {
            $this->addError($attribute, Yii::t('podium/view', 'Password must contain uppercase and lowercase letter, digit, and be at least 6 characters long.'));
        }
    }
    
    /**
     * Returns ID of current logged user.
     * @return integer
     */
    public static function loggedId()
    {
        if (Yii::$app->user->isGuest) {
            return null;
        }
        
        if (Podium::getInstance()->userComponent == Podium::USER_INHERIT) {
            $user = static::findMe();
            if ($user) {
                return $user->id;
            }
            return null;
        }
        else {
            return Yii::$app->user->id;
        }
    }

    /**
     * Bans account.
     * @return boolean
     */
    public function ban()
    {
        $this->scenario = 'ban';
        $this->status   = self::STATUS_BANNED;
        return $this->save();
    }
    
    /**
     * Demotes user to given role.
     * @param integer $role
     * @return boolean
     */
    public function demoteTo($role)
    {
        $transaction = static::getDb()->beginTransaction();
        try {
            $this->scenario = 'role';
            $this->role     = $role;            
            if ($this->save()) {
                if (Yii::$app->authManager->getRolesByUser($this->id)) {
                    Yii::$app->authManager->revoke(Yii::$app->authManager->getRole(Rbac::ROLE_MODERATOR), $this->id);
                }
                if (Yii::$app->authManager->assign(Yii::$app->authManager->getRole(Rbac::ROLE_USER), $this->id)) {
                    Yii::$app->db->createCommand()->delete(Mod::tableName(), 'user_id = :id', [':id' => $this->id])->execute();
                    Activity::updateRole($this->id, User::ROLE_MEMBER);
                    $transaction->commit();
                    Log::info('User demoted', $this->id, __METHOD__);
                    return true;
                }
            }
        }
        catch (Exception $e) {
            $transaction->rollBack();
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;        
    }
    
    /**
     * Promotes user to given role.
     * @param integer $role
     * @return boolean
     */
    public function promoteTo($role)
    {
        $transaction = static::getDb()->beginTransaction();
        try {
            $this->scenario = 'role';
            $this->role     = $role;
            if ($this->save()) {
                if (Yii::$app->authManager->getRolesByUser($this->id)) {
                    Yii::$app->authManager->revoke(Yii::$app->authManager->getRole(Rbac::ROLE_USER), $this->id);
                }
                if (Yii::$app->authManager->assign(Yii::$app->authManager->getRole(Rbac::ROLE_MODERATOR), $this->id)) {
                    Activity::updateRole($this->id, User::ROLE_MODERATOR);
                    $transaction->commit();
                    Log::info('User promoted', $this->id, __METHOD__);
                    return true;
                }
            }
        }
        catch (Exception $e) {
            $transaction->rollBack();
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }
    
    /**
     * Unbans account.
     * @return boolean
     */
    public function unban()
    {
        $this->setScenario('ban');
        $this->status = self::STATUS_ACTIVE;
        return $this->save();
    }
    
    /**
     * Registers new account.
     * @return boolean
     */
    public function register()
    {
        $this->setPassword($this->password);
        $this->generateActivationToken();
        $this->generateAuthKey();
        $this->status = self::STATUS_REGISTERED;
        return $this->save();
    }
    
    /**
     * Removes activation token.
     */
    public function removeActivationToken()
    {
        $this->activation_token = null;
    }
    
    /**
     * Removes email token.
     */
    public function removeEmailToken()
    {
        $this->email_token = null;
    }
    
    /**
     * Removes password reset token.
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }
    
    /**
     * Saves user account details changes.
     * @return boolean
     */
    public function saveChanges()
    {
        if ($this->new_password) {
            $this->setPassword($this->new_password);
        }
        if ($this->new_email) {
            $this->generateEmailToken();
        }
        $updateActivityName = $this->isAttributeChanged('username');
        if ($this->save(false)) {
            if ($updateActivityName) {
                Activity::updateName($this->id, $this->podiumName, $this->podiumSlug);
            }
            return true;
        }
        else {
            return false;
        }
    }
    
    /**
     * Generates password hash from unencrypted password.
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }
    
    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }
    
    /**
     * Validates current password.
     * @param string $attribute
     */
    public function validateCurrentPassword($attribute)
    {
        if (!$this->hasErrors()) {
            if (!$this->validatePassword($this->current_password)) {
                $this->addError($attribute, Yii::t('podium/view', 'Current password is incorrect.'));
            }
        }
    }
    
    /**
     * Validates password.
     * In case of inherited user component password hash is compared to 
     * password hash stored in Yii::$app->user->identity so this method should 
     * not be used with User instance other than currently logged in one.
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        $podium = Podium::getInstance();
        if ($podium->userComponent == Podium::USER_INHERIT) {
            $password_hash = Podium::FIELD_PASSWORD;
            if (!empty($podium->userPasswordField)) {
                $password_hash = $podium->userPasswordField;
            }
            if (!empty(Yii::$app->user->identity->$password_hash)) {
                return Yii::$app->security->validatePassword($password, Yii::$app->user->identity->$password_hash);
            }
            return false;
        }
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }
    
    /**
     * Validates username.
     * Custom method is required because JS ES5 (and so do Yii 2) doesn't support regex unicode features.
     * @param string $attribute
     */
    public function validateUsername($attribute)
    {
        if (!$this->hasErrors()) {
            if (!preg_match('/^[\p{L}][\w\p{L}]{2,254}$/u', $this->username)) {
                $this->addError($attribute, Yii::t('podium/view', 'Username must start with a letter, contain only letters, digits and underscores, and be at least 3 characters long.'));
            }
        }
    }
    
    /**
     * Implementation of \yii\web\User::can().
     * @param string $permissionName the name of the permission (e.g. "edit post") that needs access check.
     * @param array $params name-value pairs that would be passed to the rules associated
     * with the roles and permissions assigned to the user. A param with name 'user' is added to
     * this array, which holds the value of [[id]].
     * @param boolean $allowCaching whether to allow caching the result of access check.
     * When this parameter is true (default), if the access check of an operation was performed
     * before, its result will be directly returned when calling this method to check the same
     * operation. If this parameter is false, this method will always call
     * [[\yii\rbac\ManagerInterface::checkAccess()]] to obtain the up-to-date access result. Note that this
     * caching is effective only within the same request and only works when `$params = []`.
     * @return boolean whether the user can perform the operation as specified by the given permission.
     */
    public static function can($permissionName, $params = [], $allowCaching = true)
    {
        if (Podium::getInstance()->userComponent == Podium::USER_INHERIT) {
            $user = static::findMe();
            if ($allowCaching && empty($params) && isset($user->_access[$permissionName])) {
                return $user->_access[$permissionName];
            }
            $access = Yii::$app->authManager->checkAccess($user->id, $permissionName, $params);
            if ($allowCaching && empty($params)) {
                $user->_access[$permissionName] = $access;
            }
            return $access;
        }
        return Yii::$app->user->can($permissionName, $params, $allowCaching);
    }
    
    /**
     * Returns list of friends for dropdown.
     * @return array
     * @since 0.2
     */
    public static function friendsList()
    {
        if (Yii::$app->user->isGuest) {
            return null;
        }

        $cache = Podium::getInstance()->cache->getElement('user.friends', static::loggedId());
        if ($cache === false) {
            $cache = [];
            $friends = static::findMe()->friends;
            if ($friends) {
                foreach ($friends as $friend) {
                    $cache[$friend->id] = $friend->getPodiumTag(true);
                }
            }
            Podium::getInstance()->cache->setElement('user.friends', static::loggedId(), $cache);
        }
        return $cache;
    }
    
    /**
     * Updates moderator assignment for given forum.
     * @param integer $forum_id forum's ID
     * @return boolean
     * @since 0.2
     */
    public function updateModeratorForOne($forum_id = null)
    {
        try {
            if ((new Query)->from(Mod::tableName())->where(['forum_id' => $forum_id, 'user_id' => $this->id])->exists()) {
                Yii::$app->db->createCommand()->delete(Mod::tableName(), ['forum_id' => $forum_id, 'user_id' => $this->id])->execute();
            }
            else {
                Yii::$app->db->createCommand()->insert(Mod::tableName(), ['forum_id' => $forum_id, 'user_id' => $this->id])->execute();
            }
            Podium::getInstance()->cache->deleteElement('forum.moderators', $forum_id);
            Log::info('Moderator updated', $this->id, __METHOD__);
            return true;
        }
        catch (Exception $e) {
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }
    
    /**
     * Updates moderator assignment for given forums.
     * @param array $newForums new assigned forums' IDs
     * @param array $oldForums old assigned forums' IDs
     * @return boolean
     * @since 0.2
     */
    public function updateModeratorForMany($newForums = [], $oldForums = [])
    {
        try {
            $add = [];
            foreach ($newForums as $forum) {
                if (!in_array($forum, $oldForums)) {
                    if ((new Query)->from(Forum::tableName())->where(['id' => $forum])->exists() && (new Query)->from(Mod::tableName())->where(['forum_id' => $forum, 'user_id' => $this->id])->exists() === false) {
                        $add[] = [$forum, $this->id];
                    }
                }
            }
            $remove = [];
            foreach ($oldForums as $forum) {
                if (!in_array($forum, $newForums)) {
                    if ((new Query)->from(Mod::tableName())->where(['forum_id' => $forum, 'user_id' => $this->id])->exists()) {
                        $remove[] = $forum;
                    }
                }
            }
            if (!empty($add)) {
                Yii::$app->db->createCommand()->batchInsert(Mod::tableName(), ['forum_id', 'user_id'], $add)->execute();
            }
            if (!empty($remove)) {
                Yii::$app->db->createCommand()->delete(Mod::tableName(), ['forum_id' => $remove, 'user_id' => $this->id])->execute();
            }
            Podium::getInstance()->cache->delete('forum.moderators');
            Log::info('Moderators updated', null, __METHOD__);
            return true;
        }
        catch (Exception $e) {
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }
    
    /**
     * Creates inherited account.
     * @return boolean
     * @since 0.2
     */
    public static function createInheritedAccount()
    {
        try {
            if (!Yii::$app->user->isGuest) {
                $new = new User;
                $new->scenario     = 'installation';
                $new->inherited_id = Yii::$app->user->id;
                $new->status       = self::STATUS_ACTIVE;
                $new->role         = self::ROLE_MEMBER;
                $new->timezone     = self::DEFAULT_TIMEZONE;
                if (!$new->save()) {
                    throw new Exception('Account creating error');
                }
                Yii::$app->authManager->assign(Yii::$app->authManager->getRole(Rbac::ROLE_USER), $new->id);
                Cache::clearAfter('activate');
                Log::info('Inherited account created', $new->id, __METHOD__);
                return true;
            }
        }
        catch (Exception $e) {
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }
    
    /**
     * Returns JSON list of members matching query.
     * @param string $query
     * @return string
     * @since 0.2
     */
    public static function getMembersList($query = null)
    {
        if (is_null($query) || !is_string($query)) {
            return Json::encode(['results' => []]);
        }
        
        $cache = Podium::getInstance()->cache->get('members.fieldlist');
        if ($cache === false || empty($cache[$query])) {
            if ($cache === false) {
                $cache = [];
            }
            $users = static::find()->andWhere(['status' => self::STATUS_ACTIVE]);
            $users->andWhere(['!=', 'id', static::loggedId()]);
            if (preg_match('/^(forum|orum|rum|um|m)?#([0-9]+)$/', strtolower($query), $matches)) {
                $users->andWhere(['username' => ['', null], 'id' => $matches[2]]);
            }
            elseif (preg_match('/^([0-9]+)$/', $query, $matches)) {
                $users->andWhere([
                    'or', 
                    ['like', 'username', $query],
                    [
                        'username' => ['', null],
                        'id'       => $matches[1]
                    ]
                ]);
            }
            else {
                $users->andWhere(['like', 'username', $query]);
            }
            $users->orderBy(['username' => SORT_ASC, 'id' => SORT_ASC]);
            $results = ['results' => []];
            foreach ($users->each() as $user) {
                $results['results'][] = ['id' => $user->id, 'text' => $user->getPodiumTag(true)];
            }
            if (!empty($results['results'])) {
                $cache[$query] = Json::encode($results);
                Podium::getInstance()->cache->set('members.fieldlist', $cache);
            }
            else {
                return Json::encode(['results' => []]);
            }
        }

        return $cache[$query];
    }
    
    /**
     * Updates ignore status for the user.
     * @return boolean
     * @since 0.2
     */
    public function updateIgnore()
    {
        try {
            if ($this->isIgnoredBy(User::loggedId())) {
                Yii::$app->db->createCommand()->delete('{{%podium_user_ignore}}', 'user_id = :uid AND ignored_id = :iid', [':uid' => User::loggedId(), ':iid' => $this->id])->execute();
                Log::info('User unignored', $this->id, __METHOD__);
            }
            else {
                Yii::$app->db->createCommand()->insert('{{%podium_user_ignore}}', ['user_id' => User::loggedId(), 'ignored_id' => $this->id])->execute();
                Log::info('User ignored', $this->id, __METHOD__);
            }
            return true;
        }
        catch (Exception $e) {
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }
    
    /**
     * Updates friend status for the user.
     * @return boolean
     * @since 0.2
     */
    public function updateFriend()
    {
        try {
            if ($this->isBefriendedBy(User::loggedId())) {
                Yii::$app->db->createCommand()->delete('{{%podium_user_friend}}', 'user_id = :uid AND friend_id = :iid', [':uid' => User::loggedId(), ':iid' => $this->id])->execute();
                Log::info('User unfriended', $this->id, __METHOD__);
            }
            else {
                Yii::$app->db->createCommand()->insert('{{%podium_user_friend}}', ['user_id' => User::loggedId(), 'friend_id' => $this->id])->execute();
                Log::info('User befriended', $this->id, __METHOD__);
            }
            Podium::getInstance()->cache->deleteElement('user.friends', $this->id);
            return true;
        }
        catch (Exception $e) {
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }
}
