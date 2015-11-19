<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\components;

use bizley\podium\models\Activity;
use bizley\podium\models\Message;
use bizley\podium\models\Meta;
use bizley\podium\models\Mod;
use bizley\podium\models\Post;
use bizley\podium\models\Subscription;
use bizley\podium\models\Thread;
use Yii;
use yii\base\Component;
use yii\base\InvalidValueException;
use yii\db\Query;

/**
 * PodiumUser component
 *
 * Base User object for Podium.
 * User Identity class is provided with Yii::$app->user->identityClass component.
 * By default User Identity class is the instance of 
 * [[\bizley\podium\models\User]] but it can be changed by setting 
 * [[\bizley\podium\Module::$userComponent]] to 'inherit'. Podium then takes 
 * whatever is set to be User Identity class as long as it implements 
 * [[\bizley\podium\components\PodiumUserInterface]].
 */
class PodiumUser extends Component
{

    /**
     * @var \bizley\podium\models\User|mixed User object.
     */
    private $_user;
    
    /**
     * @var string User Identity class.
     */
    private $_userClass;
    
    /**
     * Bans user.
     * @return boolean
     */
    public function ban()
    {
        return $this->user->podiumBan();
    }
    
    /**
     * Delets user.
     * @return boolean
     */
    public function delete()
    {
        return $this->user->podiumDelete();
    }
    
    /**
     * Demotes user to given role.
     * @see [[\bizley\podium\models\User::getRoles()]]
     * @param integer $role role ID
     * @return boolean
     */
    public function demoteTo($role)
    {
        return $this->user->podiumDemoteTo($role);
    }
    
    /**
     * Finds Podium moderator.
     * @param integer|array $id user ID or array of conditions
     * @return \bizley\podium\components\PodiumUser
     */
    public function findModerator($id)
    {
        $find = $this->user->podiumFindModerator($id);
        if ($find instanceof $this->_userClass) {
            $this->user = $find;
        }
        return $this;
    }
    
    /**
     * Finds Podium user.
     * @param integer|array $id user ID or array of conditions
     * @return \bizley\podium\components\PodiumUser
     */
    public function findOne($id)
    {
        $find = $this->user->podiumFindOne($id);
        if ($find instanceof $this->_userClass) {
            $this->user = $find;
        }
        return $this;
    }
    
    /**
     * Sets relation with Activity.
     * @return Activity
     */
    public function getActivity()
    {
        return $this->user->hasOne(Activity::className(), ['user_id' => $this->getIdAttribute()])->limit(1)->one();
    }
    
    /**
     * Gets user anonymous attribute.
     * @return integer
     */
    public function getAnonymous()
    {
        return $this->user->getPodiumAnonymous();
    }
    
    /**
     * Gets user created_at attribute.
     * @return integer
     */
    public function getCreatedAt()
    {
        return $this->user->getPodiumCreatedAt();
    }
    
    /**
     * Gets user email attribute.
     * @return string
     */
    public function getEmail()
    {
        return $this->user->getPodiumEmail();
    }
    
    /**
     * Gets user ID attribute.
     * @return integer
     */
    public function getId()
    {
        return $this->user->getPodiumId();
    }
    
    /**
     * Gets user ID attribute's name.
     * @return string
     */
    public function getIdAttribute()
    {
        return $this->user->getPodiumIdAttribute();
    }
    
    /**
     * Sets relation with Meta.
     * @return Meta
     */
    public function getMeta()
    {
        return $this->user->hasOne(Meta::className(), ['user_id' => $this->getIdAttribute()])->limit(1)->one();
    }
    
    /**
     * Gets all Podium moderators.
     * @return \yii\db\ActiveQuery
     */
    public function getModerators()
    {
        return $this->user->getPodiumModerators();
    }
    
    /**
     * Sets relation with Mod.
     * @return Mod[]
     */
    public function getMods()
    {
        return $this->user->hasMany(Mod::className(), ['user_id' => $this->getIdAttribute()])->all();
    }
    
    /**
     * Gets Podium name.
     * @return string
     */
    public function getName()
    {
        return $this->user->getPodiumName();
    }
    
    /**
     * Returns number of new user messages.
     * @return integer
     */
    public function getNewMessagesCount()
    {
        $cache = Cache::getInstance()->getElement('user.newmessages', $this->getId());
        if ($cache === false) {
            $cache = (new Query)->from(Message::tableName())->where(['receiver_id' => $this->getId(),
                        'receiver_status' => Message::STATUS_NEW])->count();
            Cache::getInstance()->setElement('user.newmessages', $this->getId(), $cache);
        }

        return $cache;
    }
    
    /**
     * Gets newest registered members.
     * @param integer $limit number of members to fetch
     * @return \yii\db\ActiveQuery
     */
    public function getNewest($limit = 10)
    {
        return $this->user->getPodiumNewest($limit);
    }
    
    /**
     * Gets number of active posts added by user.
     * @return integer
     */
    public function getPostsCount($id = null)
    {
        $cache = Cache::getInstance()->getElement('user.postscount', empty($id) ? $this->getId() : $id);
        if ($cache === false) {
            $cache = (new Query)->from(Post::tableName())->where(['author_id' => empty($id) ? $this->getId() : $id])->count();
            Cache::getInstance()->setElement('user.postscount', empty($id) ? $this->getId() : $id, $cache);
        }

        return $cache;
    }
    
    /**
     * Gets user role attribute.
     * @return integer
     */
    public function getRole()
    {
        return $this->user->getPodiumRole();
    }
    
    /**
     * Gets user slug attribute.
     * @return string
     */
    public function getSlug()
    {
        return $this->user->getPodiumSlug();
    }
    
    /**
     * Gets user status attribute.
     * @return integer
     */
    public function getStatus()
    {
        return $this->user->getPodiumStatus();
    }
    
    /**
     * Gets number of user subscribed threads with new posts.
     * @return integer
     */
    public function getSubscriptionsCount()
    {
        $cache = Cache::getInstance()->getElement('user.subscriptions', $this->getId());
        if ($cache === false) {
            $cache = (new Query)->from(Subscription::tableName())->where(['user_id' => $this->getId(),
                        'post_seen' => Subscription::POST_NEW])->count();
            Cache::getInstance()->setElement('user.subscriptions', $this->getId(), $cache);
        }

        return $cache;
    }
    
    /**
     * Gets Podium tag.
     * @param boolean $simple whether prepare simple tag
     * @return string
     */
    public function getTag($simple = false)
    {
        return $this->user->getPodiumTag($simple);
    }
    
    /**
     * Gets number of active threads added by user.
     * @return integer
     */
    public function getThreadsCount($id = null)
    {
        $cache = Cache::getInstance()->getElement('user.threadscount', empty($id) ? $this->getId() : $id);
        if ($cache === false) {
            $cache = (new Query)->from(Thread::tableName())->where(['author_id' => empty($id) ? $this->getId() : $id])->count();
            Cache::getInstance()->setElement('user.threadscount', empty($id) ? $this->getId() : $id, $cache);
        }

        return $cache;
    }
    
    /**
     * Gets user timezone attribute.
     * @return string
     */
    public function getTimeZone()
    {
        return $this->user->getPodiumTimeZone();
    }
    
    /**
     * Gets user object.
     * @return \bizley\podium\models\User|mixed
     */
    public function getUser()
    {
        return $this->_user;
    }
    
    /**
     * Gets user class.
     * @return string
     */
    public function getUserClass()
    {
        return $this->_userClass;
    }
    
    /**
     * Initiates component and gets user class set as global component.
     */
    public function init()
    {
        parent::init();
        $this->setUserComponent();
    }
    
    /**
     * Finds out if user is ignored by another.
     * @param integer $user_id user ID
     * @return boolean
     */
    public function isIgnoredBy($user_id)
    {
        if ((new Query)->select('id')->from('{{%podium_user_ignore}}')->where(['user_id' => $user_id,
                    'ignored_id' => $this->getId()])->exists()) {
            return true;
        }
        return false;
    }
    
    /**
     * Promotes user to given role.
     * @see [[\bizley\podium\models\User::getRoles()]]
     * @param integer $role role ID
     * @return boolean
     */
    public function promoteTo($role)
    {
        return $this->user->podiumPromoteTo($role);
    }
    
    /**
     * Sets user object.
     * @param \bizley\podium\models\User|mixed $user
     */
    public function setUser($user)
    {
        $this->_user = $user;
    }
    
    /**
     * Sets user component.
     * @throws InvalidValueException
     */
    public function setUserComponent()
    {
        if (new Yii::$app->user->identityClass instanceof PodiumUserInterface) {
            $this->_userClass = Yii::$app->user->identityClass;
            $this->setUser(new $this->_userClass);
        }
        else {
            throw new InvalidValueException('The identityClass must implement PodiumUserInterface.');
        }
    }
    
    /**
     * Unbans user.
     * @return boolean
     */
    public function unban()
    {
        return $this->user->podiumUnban();
    }
    
    /**
     * Prepares user search objects.
     * @param array $params search parameters
     * @param boolean $active whether look only for active users
     * @param boolean $mods whether look only for moderators
     * @return array
     */
    public function userSearch($params, $active = false, $mods = false)
    {
        return $this->user->podiumUserSearch($params, $active, $mods);
    }
}