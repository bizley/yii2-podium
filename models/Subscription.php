<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\models;

use bizley\podium\components\Cache;
use bizley\podium\components\Config;
use bizley\podium\log\Log;
use Exception;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Subscription model
 *
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 * @property integer $id
 * @property integer $user_id
 * @property integer $thread_id
 * @property integer $post_seen
 */
class Subscription extends ActiveRecord
{

    const POST_SEEN = 1;
    const POST_NEW  = 0;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%podium_subscription}}';
    }

    /**
     * User relation.
     * @return User
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
    
    /**
     * Thread relation.
     * @return Thread
     */
    public function getThread()
    {
        return $this->hasOne(Thread::className(), ['id' => 'thread_id']);
    }
    
    /**
     * Searches for subscription
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = self::find()->where(['user_id' => User::loggedId()]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => 10,
                'forcePageParam' => false
            ],
        ]);

        $dataProvider->sort->defaultOrder = ['post_seen' => SORT_ASC, 'id' => SORT_DESC];
        $dataProvider->pagination->pageSize = Yii::$app->session->get('per-page', 20);

        return $dataProvider;
    }
    
    /**
     * Marks post as seen.
     * @return boolean
     */
    public function seen()
    {
        $this->post_seen = self::POST_SEEN;
        if ($this->save()) {
            Cache::getInstance()->deleteElement('user.subscriptions', User::loggedId());
            return true;
        }
        return false;
    }
    
    /**
     * Marks post as unseen.
     * @return boolean
     */
    public function unseen()
    {
        $this->post_seen = self::POST_NEW;
        if ($this->save()) {
            Cache::getInstance()->deleteElement('user.subscriptions', User::loggedId());
            return true;
        }
        return false;
    }
    
    /**
     * Prepares notification email.
     * @param integer $thread
     */
    public static function notify($thread)
    {
        if (is_numeric($thread) && $thread > 0) {            
            $forum = Config::getInstance()->get('name');
            $email = Content::fill(Content::EMAIL_SUBSCRIPTION);
            
            $subs = static::find()->where(['thread_id' => $thread, 'post_seen' => self::POST_SEEN]);
            foreach ($subs->each() as $sub) {
                $sub->post_seen = self::POST_NEW;
                if ($sub->save()) {
                    if ($email !== false && !empty($sub->user->email)) {
                        if (Email::queue(
                                $sub->user->email, 
                                str_replace('{forum}', $forum, $email->topic),
                                str_replace('{forum}', $forum, str_replace('{link}', Html::a(
                                        Url::to(['default/last', 'id' => $sub->thread_id], true),
                                        Url::to(['default/last', 'id' => $sub->thread_id], true)
                                    ), $email->content)),
                                $sub->user_id
                            )) {
                            Log::info('Subscription notice link queued', $sub->user_id, __METHOD__);
                        }
                        else {
                            Log::error('Error while queuing subscription notice link', $sub->user_id, __METHOD__);
                        }
                    }
                    else {
                        Log::error('Error while queuing subscription notice link - no email set', $sub->user_id, __METHOD__);
                    }
                }
            }
        }
    }
    
    /**
     * Removes threads' subscriptions of given IDs.
     * @param array $threads threads' IDs
     * @return boolean
     * @since 0.2
     */
    public static function remove($threads = [])
    {
        try {
            if (!empty($threads)) {
                Yii::$app->db->createCommand()->delete(Subscription::tableName(), ['id' => $threads, 'user_id' => User::loggedId()])->execute();
                return true;
            }
        }
        catch (Exception $e) {
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }
    
    /**
     * Adds subscription for thread.
     * @param integer $thread thread's ID
     * @return boolean
     * @since 0.2
     */
    public static function add($thread)
    {
        if (!Yii::$app->user->isGuest) {
            $sub = new Subscription;
            $sub->thread_id = $thread;
            $sub->user_id   = User::loggedId();
            $sub->post_seen = self::POST_SEEN;
            if ($sub->save()) {
                return true;
            }
        }
        return false;
    }
}
