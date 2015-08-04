<?php

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
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\web\IdentityInterface;

/**
 * PodiumUser component
 *
 * Base User object for Podium.
 * User Identity is provided with Yii::$app->user->identity component.
 * By default User Identity is the instance of \bizley\podium\models\User but 
 * it can be changed by setting \bizley\podium\Module::userComponent to 
 * 'inherit'. Podium then takes whatever is set to be User Identity as long as 
 * it implements IdentityInterface, PodiumUserInterface and extends 
 * ActiveRecord class.
 */
class PodiumUser extends Component
{

    private $_identity;
    
    public function ban()
    {
        return $this->identity->podiumBan();
    }
    
    public function delete()
    {
        return $this->identity->podiumDelete();
    }
    
    public function demoteTo($role)
    {
        return $this->identity->podiumDemoteTo($role);
    }
    
    public function findModerator($id)
    {
        return $this->identity->podiumFindModerator($id);
    }
    
    public function findOne($id)
    {
        return $this->identity->podiumFindOne($id);
    }
    
    /**
     * Relation with Activity.
     * @return \yii\db\ActiveQuery
     */
    public function getActivity()
    {
        return $this->identity->hasOne(Activity::className(), ['user_id' => $this->identity->primaryKey]);
    }
    
    public function getAnonymous()
    {
        return $this->identity->getPodiumAnonymous();
    }
    
    public function getEmail()
    {
        return $this->identity->getPodiumEmail();
    }
    
    public function getId()
    {
        return $this->identity->getId();
    }
    
    public function getIdentity()
    {
        return $this->identity;
    }
    
    /**
     * Relation with Meta.
     * @return \yii\db\ActiveQuery
     */
    public function getMeta()
    {
        return $this->identity->hasOne(Meta::className(), ['user_id' => $this->identity->primaryKey]);
    }

    public function getModerators()
    {
        return $this->identity->getPodiumModerators();
    }
    
    /**
     * Relation with Mod.
     * @return \yii\db\ActiveQuery
     */
    public function getMods()
    {
        return $this->identity->hasMany(Mod::className(), ['user_id' => $this->identity->primaryKey]);
    }
    
    /**
     * Newest registered members.
     * @return \yii\db\ActiveQuery
     */
    public function getNewest($limit = 10)
    {
        return $this->identity->getPodiumNewest($limit);
    }
    
    /**
     * Returns number of new messages.
     * @return int
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
     * Returns Podium name.
     * @return string
     */
    public function getPodiumName()
    {
        return $this->identity->getPodiumName();
    }
    
    /**
     * Returns Podium tag.
     * @return string
     */
    public function getPodiumTag($simple = false)
    {
        return $this->identity->getPodiumTag($simple);
    }
    
    /**
     * Returns number of added posts.
     * @return int
     */
    public function getPostsCount()
    {
        $cache = Cache::getInstance()->getElement('user.postscount', $this->getId());
        if ($cache === false) {
            $cache = (new Query)->from(Post::tableName())->where(['author_id' => $this->getId()])->count();
            Cache::getInstance()->setElement('user.postscount', $this->getId(), $cache);
        }

        return $cache;
    }
    
    public function getRole()
    {
        return $this->identity->getPodiumRole();
    }
    
    public function getSlug()
    {
        return $this->identity->getPodiumSlug();
    }
    
    public function getStatus()
    {
        return $this->identity->getPodiumStatus();
    }
    
    /**
     * Returns number of subscibed threads with new posts.
     * @return int
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
     * Returns number of added threads.
     * @return int
     */
    public function getThreadsCount()
    {
        return (new Query)->from(Thread::tableName())->where(['author_id' => $this->getId()])->count();
    }
    
    public function getTimeZone()
    {
        return $this->identity->getPodiumTimeZone();
    }
    
    public function init()
    {
        parent::init();
        $this->setIdentity(Yii::$app->user->identity);
    }


    /**
     * Finds out if user is ignored by another.
     * @param int $user_id user ID
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

    public function promoteTo($role)
    {
        return $this->identity->podiumPromoteTo($role);
    }
    
    public function setIdentity($identity)
    {
        if ($identity instanceof IdentityInterface) {
            if ($identity instanceof PodiumUserInterface) {
                if ($identity instanceof ActiveRecord) {
                    $this->_identity = $identity;
                }
                else {
                    throw new InvalidValueException('The identity object must be instance of ActiveRecord.');
                }
            }
            else {
                throw new InvalidValueException('The identity object must implement PodiumUserInterface.');
            }
        }
        elseif ($identity === null) {
            $this->_identity = null;
        }
        else {
            throw new InvalidValueException('The identity object must implement IdentityInterface.');
        }
    }
    
    public function unban()
    {
        return $this->identity->podiumUnban();
    }
    
    public function userSearch($params, $active = false, $mods = false)
    {
        return $this->identity->podiumUserSearch($params, $active, $mods);
    }
}