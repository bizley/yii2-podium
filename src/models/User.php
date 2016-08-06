<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\models;

use bizley\podium\components\Cache;
use bizley\podium\components\Helper;
use bizley\podium\log\Log;
use bizley\podium\Module as Podium;
use bizley\podium\rbac\Rbac;
use Exception;
use himiklab\yii2\recaptcha\ReCaptchaValidator;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Query;
use yii\helpers\Json;
use Zelenin\yii\behaviors\Slug;

/**
 * User model
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 * 
 * @property string $current_password write-only password
 * @property string $password write-only password
 * @property string $password_repeat write-only password repeated
 * @property integer $tos write-only terms of service agreement
 */
class User extends BaseUser
{
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
            ['tos', 'compare', 'compareValue' => 1, 'message' => Yii::t('podium/view', 'You have to read and agree on ToS.')],
        ];
        
        if (Podium::getInstance()->config->get('recaptcha_sitekey') !== '' && Podium::getInstance()->config->get('recaptcha_secretkey') !== '') {
            $rules[] = ['captcha', ReCaptchaValidator::className(), 'secret' => Podium::getInstance()->config->get('recaptcha_secretkey')];
        } else {
            $rules[] = ['captcha', 'captcha', 'captchaAction' => 'podium/account/captcha'];
        }
        
        return $rules;
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
     * Activates account.
     * @return boolean
     */
    public function activate()
    {
        $transaction = static::getDb()->beginTransaction();
        try {
            if ($this->status == self::STATUS_REGISTERED) {
                $this->removeActivationToken();
                $this->status = self::STATUS_ACTIVE;
                if ($this->save()) {
                    if (Yii::$app->authManager->assign(Yii::$app->authManager->getRole(Rbac::ROLE_USER), $this->id)) {
                        $transaction->commit();
                        return true;
                    }
                }
            }
        } catch (Exception $e) {
            $transaction->rollBack();
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }
    
    /**
     * Changes email address.
     * @return boolean
     */
    public function changeEmail()
    {
        $this->email = $this->new_email;
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
     * Returns number of new user messages.
     * @return integer
     */
    public function getNewMessagesCount()
    {
        $cache = Podium::getInstance()->cache->getElement('user.newmessages', $this->id);
        if ($cache === false) {
            $cache = (new Query)->from(MessageReceiver::tableName())->where([
                'receiver_id'     => $this->id,
                'receiver_status' => Message::STATUS_NEW
            ])->count();
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
            $cache = (new Query)->from(Subscription::tableName())->where([
                'user_id'   => $this->id,
                'post_seen' => Subscription::POST_NEW
            ])->count();
            Podium::getInstance()->cache->setElement('user.subscriptions', $this->id, $cache);
        }

        return $cache;
    }
    
    /**
     * Finds out if user is befriended by another.
     * @param integer $user_id user ID
     * @return boolean
     * @since 0.2
     */
    public function isBefriendedBy($user_id)
    {
        if ((new Query)->select('id')->from('{{%podium_user_friend}}')->where([
                'user_id'   => $user_id, 
                'friend_id' => $this->id
            ])->exists()) {
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
        if ((new Query)->select('id')->from('{{%podium_user_friend}}')->where([
                'user_id'   => $this->id, 
                'friend_id' => $user_id
            ])->exists()) {
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
        if ((new Query)->select('id')->from('{{%podium_user_ignore}}')->where([
                'user_id'    => $user_id, 
                'ignored_id' => $this->id
            ])->exists()) {
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
        if ((new Query)->select('id')->from('{{%podium_user_ignore}}')->where([
                'user_id'    => $this->id, 
                'ignored_id' => $user_id
            ])->exists()) {
            return true;
        }
        return false;
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
        } else {
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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
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
        return false;
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
        if (Podium::getInstance()->userComponent == Podium::USER_INHERIT && !Yii::$app->user->isGuest) {
            $user = static::findMe();
            if ($user) {
                if ($allowCaching && empty($params) && isset($user->_access[$permissionName])) {
                    return $user->_access[$permissionName];
                }
                $access = Yii::$app->authManager->checkAccess($user->id, $permissionName, $params);
                if ($allowCaching && empty($params)) {
                    $user->_access[$permissionName] = $access;
                }
                return $access;
            }
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
            if ((new Query)->from(Mod::tableName())->where([
                    'forum_id' => $forum_id, 
                    'user_id'  => $this->id
                ])->exists()) {
                Yii::$app->db->createCommand()->delete(Mod::tableName(), [
                    'forum_id' => $forum_id, 
                    'user_id'  => $this->id
                ])->execute();
            } else {
                Yii::$app->db->createCommand()->insert(Mod::tableName(), [
                    'forum_id' => $forum_id, 
                    'user_id'  => $this->id
                ])->execute();
            }
            Podium::getInstance()->cache->deleteElement('forum.moderators', $forum_id);
            Log::info('Moderator updated', $this->id, __METHOD__);
            return true;
        } catch (Exception $e) {
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
                    if ((new Query)->from(Forum::tableName())
                            ->where(['id' => $forum])
                            ->exists() 
                            && (new Query)->from(Mod::tableName())
                                ->where(['forum_id' => $forum, 'user_id' => $this->id])
                                ->exists() === false) {
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
        } catch (Exception $e) {
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
                $new->setScenario('installation');
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
        } catch (Exception $e) {
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
            } elseif (preg_match('/^([0-9]+)$/', $query, $matches)) {
                $users->andWhere([
                    'or', 
                    ['like', 'username', $query],
                    [
                        'username' => ['', null],
                        'id'       => $matches[1]
                    ]
                ]);
            } else {
                $users->andWhere(['like', 'username', $query]);
            }
            $users->orderBy(['username' => SORT_ASC, 'id' => SORT_ASC]);
            $results = ['results' => []];
            foreach ($users->each() as $user) {
                $results['results'][] = ['id' => $user->id, 'text' => $user->getPodiumTag(true)];
            }
            if (empty($results['results'])) {
                return Json::encode(['results' => []]);
            }
            $cache[$query] = Json::encode($results);
            Podium::getInstance()->cache->set('members.fieldlist', $cache);
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
                Yii::$app->db->createCommand()->delete(
                        '{{%podium_user_ignore}}', 
                        'user_id = :uid AND ignored_id = :iid', 
                        [':uid' => User::loggedId(), ':iid' => $this->id]
                    )->execute();
                Log::info('User unignored', $this->id, __METHOD__);
            } else {
                Yii::$app->db->createCommand()->insert('{{%podium_user_ignore}}', ['user_id' => User::loggedId(), 'ignored_id' => $this->id])->execute();
                Log::info('User ignored', $this->id, __METHOD__);
            }
            return true;
        } catch (Exception $e) {
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
                Yii::$app->db->createCommand()->delete(
                        '{{%podium_user_friend}}', 
                        'user_id = :uid AND friend_id = :iid', 
                        [':uid' => User::loggedId(), ':iid' => $this->id]
                    )->execute();
                Log::info('User unfriended', $this->id, __METHOD__);
            } else {
                Yii::$app->db->createCommand()->insert('{{%podium_user_friend}}', ['user_id' => User::loggedId(), 'friend_id' => $this->id])->execute();
                Log::info('User befriended', $this->id, __METHOD__);
            }
            Podium::getInstance()->cache->deleteElement('user.friends', $this->id);
            return true;
        } catch (Exception $e) {
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }
}
