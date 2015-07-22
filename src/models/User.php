<?php

namespace bizley\podium\models;

use bizley\podium\components\Cache;
use bizley\podium\components\Config;
use bizley\podium\components\Helper;
use bizley\podium\components\Log;
use Exception;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
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
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $current_password write-only password
 * @property string $password write-only password
 * @property string $password_repeat write-only password repeated
 * @property integer $tos write-only terms of service agreement
 * @property string $timezone
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

    /**
     * @var string current password for profile update.
     */
    public $current_password;
    
    /**
     * @var string unencrypted password.
     */
    public $password;
    
    /**
     * @var string unencrypted password repeated.
     */
    public $password_repeat;
    
    /**
     * @var int terms of service agreement flag.
     */
    public $tos;

    /**
     * Activates account.
     * @return boolean
     */
    public function activate()
    {
        if ($this->status == self::STATUS_REGISTERED) {
            $this->removeActivationToken();
            $this->status = self::STATUS_ACTIVE;

            $transaction = self::getDb()->beginTransaction();
            try {
                if ($this->save()) {
                    
                    if (Yii::$app->authManager->assign(Yii::$app->authManager->getRole('user'), $this->id)) {
                    
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
     * Bans account.
     * @return boolean
     */
    public function ban()
    {
        $this->status = self::STATUS_BANNED;
        return $this->save();
    }
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            [
                'class'     => SluggableBehavior::className(),
                'attribute' => 'username'
            ]
        ];
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
     * Finds user by activation token.
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
     * Finds user by email.
     * @param string $email
     * @return static|null
     */
    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email, 'status' => self::STATUS_ACTIVE]);
    }
    
    /**
     * Finds user by email token.
     * @param string $token activation token
     * @return static|null
     */
    public static function findByEmailToken($token)
    {
        if (!static::isEmailTokenValid($token)) {
            return null;
        }
        return static::findOne(['email_token' => $token, 'status' => self::STATUS_ACTIVE]);
    }
    
    /**
     * Finds user by username or email.
     * @param string $keyfield value to compare
     * @return static|null
     */
    public static function findByKeyfield($keyfield, $status = self::STATUS_ACTIVE)
    {
        if ($status === null) {
            return static::find()->where(['or', ['email' => $keyfield], ['username' => $keyfield]])->one();
        }
        return static::find()->where(['and', ['status' => $status], ['or', ['email' => $keyfield], ['username' => $keyfield]]])->one();
    }
    
    /**
     * Finds user by password reset token
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
            return static::findOne(['password_reset_token' => $token]);
        }
        return static::findOne(['password_reset_token' => $token, 'status' => $status]);
    }
    
    /**
     * Finds user by username.
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }
    
    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
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
     * Relation with Activity.
     * @return \yii\db\ActiveQuery
     */
    public function getActivity()
    {
        return $this->hasOne(Activity::className(), ['user_id' => 'id']);
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
     * Relation with Meta.
     * @return \yii\db\ActiveQuery
     */
    public function getMeta()
    {
        return $this->hasOne(Meta::className(), ['user_id' => 'id']);
    }

    /**
     * Relation with Mod.
     * @return \yii\db\ActiveQuery
     */
    public function getMods()
    {
        return $this->hasMany(Mod::className(), ['user_id' => 'id']);
    }
    
    /**
     * Newest registered members.
     * @return array
     */
    public function getNewest($limit = 10)
    {
        return self::find()->orderBy(['created_at' => SORT_DESC])->limit($limit)->all();
    }
    
    /**
     * Returns number of new messages.
     * @return int
     */
    public function getNewMessagesCount()
    {
        $cache = Cache::getInstance()->getElement('user.newmessages', $this->id);
        if ($cache === false) {
            $cache = (new Query)->from(Message::tableName())->where(['receiver_id' => $this->id,
                        'receiver_status' => Message::STATUS_NEW])->count();
            Cache::getInstance()->setElement('user.newmessages', $this->id, $cache);
        }

        return $cache;
    }
    
    /**
     * Returns Podium name.
     * @return string
     */
    public function getPodiumName()
    {
        return $this->username ? $this->username : Yii::t('podium/view', 'Member#{ID}', ['ID' => $this->id]);
    }
    
    /**
     * Returns Podium name tag.
     * @param boolean $simple
     * @return string
     */
    public function getPodiumTag($simple = false)
    {
        return Helper::podiumUserTag($this->getPodiumName(), $this->role, $this->id, $this->slug, $simple);
    }
    
    /**
     * Returns number of added posts.
     * @return int
     */
    public function getPostsCount()
    {
        $cache = Cache::getInstance()->getElement('user.postscount', $this->id);
        if ($cache === false) {
            $cache = (new Query)->from(Post::tableName())->where(['author_id' => $this->id])->count();
            Cache::getInstance()->setElement('user.postscount', $this->id, $cache);
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
     * Returns number of added threads.
     * @return int
     */
    public function getThreadsCount()
    {
        return (new Query)->from(Thread::tableName())->where(['author_id' => $this->id])->count();
    }
    
    /**
     * Returns chosen time zone.
     * @return string
     */
    public function getTimeZone()
    {
        return !empty($this->timezone) ? $this->timezone : 'UTC';
    }
    
    /**
     * Finds out if activation token is valid.
     * @param string $token activation token
     * @return boolean
     */
    public static function isActivationTokenValid($token)
    {
        $expire = Config::getInstance()->get('activation_token_expire');
        if ($expire === null) {
            $expire = 3 * 24 * 60 * 60;
        }
        return self::isTokenValid($token, $expire);
    }

    /**
     * Finds out if email token is valid.
     * @param string $token activation token
     * @return boolean
     */
    public static function isEmailTokenValid($token)
    {
        $expire = Config::getInstance()->get('email_token_expire');
        if ($expire === null) {
            $expire = 24 * 60 * 60;
        }
        return self::isTokenValid($token, $expire);
    }
    
    /**
     * Finds out if user is ignored by another.
     * @param int $user_id user ID
     * @return boolean
     */
    public function isIgnoredBy($user_id)
    {
        if ((new Query)->select('id')->from('{{%podium_user_ignore}}')->where(['user_id' => $user_id,
                    'ignored_id' => $this->id])->exists()) {
            return true;
        }
        return false;
    }

    /**
     * Finds out if password reset token is valid.
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        $expire = Config::getInstance()->get('password_reset_token_expire');
        if ($expire === null) {
            $expire = 24 * 60 * 60;
        }
        return self::isTokenValid($token, $expire);
    }
    
    /**
     * Finds out if given token type is valid.
     * @param string $token activation token
     * @return boolean
     */
    public static function isTokenValid($token, $expire)
    {
        if (empty($token)) {
            return false;
        }
        $parts     = explode('_', $token);
        $timestamp = (int) end($parts);
        return $timestamp + $expire >= time();
    }

    /**
     * Finds out if unencrypted password fulfill requirements.
     */
    public function passwordRequirements()
    {
        if (!preg_match('~\p{Lu}~', $this->password) ||
                !preg_match('~\p{Ll}~', $this->password) ||
                !preg_match('~[0-9]~', $this->password) ||
                mb_strlen($this->password, 'UTF-8') < 6 ||
                mb_strlen($this->password, 'UTF-8') > 100) {
            $this->addError('password', Yii::t('podium/view', 'Password must contain uppercase and lowercase letter, digit, and be at least 6 characters long.'));
        }
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
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email', 'password', 'password_repeat', 'tos'], 'required', 'except' => ['account']],
            ['current_password', 'required'],
            ['current_password', 'validateCurrentPassword'],
            [['email', 'new_email'], 'email', 'message' => Yii::t('podium/view', 'This is not a valid e-mail address.')],
            [['email', 'new_email'], 'string', 'max' => 255, 'message' => Yii::t('podium/view', 'Provided e-mail address is too long.')],
            ['email', 'unique'],
            ['new_email', 'unique', 'targetAttribute' => 'email'],
            ['password', 'passwordRequirements'],
            ['password', 'compare'],
            ['username', 'unique'],
            ['username', 'validateUsername'],
            ['anonymous', 'boolean'],
            ['timezone', 'match', 'pattern' => '/[\w\-]+/'],
            ['status', 'default', 'value' => self::STATUS_REGISTERED],
            ['role', 'default', 'value' => self::ROLE_MEMBER],
            ['tos', 'in', 'range' => [1], 'message' => Yii::t('podium/view', 'You have to read and agree on ToS.')]
        ];
    }

    /**
     * Saves password and/or email changes.
     * @return boolean
     */
    public function saveChanges()
    {
        if ($this->password) {
            $this->setPassword($this->password);
        }
        if ($this->new_email) {
            $this->generateEmailToken();
        }
        return $this->save();
    }
    
    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'installation'   => [],
            'token'          => [],
            'ban'            => [],
            'role'           => [],
            'passwordChange' => ['password', 'password_repeat'],
            'register'       => ['email', 'password', 'password_repeat'],
            'account'        => ['username', 'anonymous', 'new_email', 'password', 'password_repeat', 'timezone', 'current_password'],
        ];
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
    public static function tableName()
    {
        return '{{%podium_user}}';
    }
    
    /**
     * Unbans account.
     * @return boolean
     */
    public function unban()
    {
        $this->status = self::STATUS_ACTIVE;
        return $this->save();
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
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
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
}