<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\models;

use bizley\podium\components\Config;
use bizley\podium\components\PodiumUser;
use bizley\podium\log\Log;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Subscription model
 *
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

    public function getPodiumUser()
    {
        return (new PodiumUser)->findOne($this->user_id);
    }
    
    public function getThread()
    {
        return $this->hasOne(Thread::className(), ['id' => 'thread_id']);
    }
    
    public function search($params)
    {
        $query = self::find()->where(['user_id' => Yii::$app->user->id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => 10,
                'forcePageParam' => false
            ],
        ]);

        $dataProvider->sort->defaultOrder = ['post_seen' => SORT_ASC, 'id' => SORT_DESC];

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        return $dataProvider;
    }
    
    public function seen()
    {
        $this->post_seen = self::POST_SEEN;
        return $this->save();
    }
    
    public function unseen()
    {
        $this->post_seen = self::POST_NEW;
        return $this->save();
    }
    
    public static function notify($thread)
    {
        if (is_numeric($thread) && $thread > 0) {
            
            $email = Content::find()->where(['name' => 'email-sub'])->one();
            if ($email) {
                $topic   = $email->topic;
                $content = $email->content;
            }
            else {
                $topic   = 'New post in subscribed thread at {forum}';
                $content = '<p>There has been new post added in the thread you are subscribing. Click the following link to read the thread.</p><p>{link}</p><p>See you soon!<br />{forum}</p>';
            }

            $forum = Config::getInstance()->get('name');
            
            $subs = static::find()->where(['thread_id' => (int)$thread, 'post_seen' => static::POST_SEEN]);
            foreach ($subs->each() as $sub) {

                $sub->post_seen = static::POST_NEW;
                if ($sub->save()) {
                    
                    if (Email::queue($sub->podiumUser->user->email, 
                            str_replace('{forum}', $forum, $topic),
                            str_replace('{forum}', $forum, str_replace('{link}', Html::a(
                                    Url::to(['default/last', 'id' => $sub->thread_id], true),
                                    Url::to(['default/last', 'id' => $sub->thread_id], true)
                                ), $content)),
                            !empty($sub->user_id) ? $sub->user_id : null
                        )) {
                        Log::info('Subscription notice link queued', !empty($sub->user_id) ? $sub->user_id : '', __METHOD__);
                    }
                    else {
                        Log::error('Error while queuing subscription notice link', !empty($sub->user_id) ? $sub->user_id : '', __METHOD__);
                    }
                }
            }
        }
    }
}
