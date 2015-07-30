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
 * PodiumUser model
 *
 * @property ActiveRecord $identity
 */
class PodiumUser extends Component
{

    private $_identity;
    
    /**
     * Relation with Activity.
     * @return \yii\db\ActiveQuery
     */
    public function getActivity()
    {
        return $this->identity->hasOne(Activity::className(), ['user_id' => $this->identity->primaryKey]);
    }
    
    public function getIdentity()
    {
        return $this->_identity;
    }
    
    /**
     * Relation with Meta.
     * @return \yii\db\ActiveQuery
     */
    public function getMeta()
    {
        return $this->identity->hasOne(Meta::className(), ['user_id' => $this->identity->primaryKey]);
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
        return $this->identity->getNewest($limit);
    }
    
    /**
     * Returns number of new messages.
     * @return int
     */
    public function getNewMessagesCount()
    {
        $cache = Cache::getInstance()->getElement('user.newmessages', $this->identity->id);
        if ($cache === false) {
            $cache = (new Query)->from(Message::tableName())->where(['receiver_id' => $this->identity->id,
                        'receiver_status' => Message::STATUS_NEW])->count();
            Cache::getInstance()->setElement('user.newmessages', $this->identity->id, $cache);
        }

        return $cache;
    }
    
    /**
     * Returns number of added posts.
     * @return int
     */
    public function getPostsCount()
    {
        $cache = Cache::getInstance()->getElement('user.postscount', $this->identity->id);
        if ($cache === false) {
            $cache = (new Query)->from(Post::tableName())->where(['author_id' => $this->identity->id])->count();
            Cache::getInstance()->setElement('user.postscount', $this->identity->id, $cache);
        }

        return $cache;
    }
    
    /**
     * Returns number of subscibed threads with new posts.
     * @return int
     */
    public function getSubscriptionsCount()
    {
        $cache = Cache::getInstance()->getElement('user.subscriptions', $this->identity->id);
        if ($cache === false) {
            $cache = (new Query)->from(Subscription::tableName())->where(['user_id' => $this->identity->id,
                        'post_seen' => Subscription::POST_NEW])->count();
            Cache::getInstance()->setElement('user.subscriptions', $this->identity->id, $cache);
        }

        return $cache;
    }
    
    /**
     * Returns number of added threads.
     * @return int
     */
    public function getThreadsCount()
    {
        return (new Query)->from(Thread::tableName())->where(['author_id' => $this->identity->id])->count();
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
                    'ignored_id' => $this->identity->id])->exists()) {
            return true;
        }
        return false;
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
    
    
}