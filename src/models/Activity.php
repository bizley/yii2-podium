<?php

/**
 * @author Bizley
 */
namespace bizley\podium\models;

use bizley\podium\components\Cache;
use Exception;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Activity model
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $username
 * @property integer $user_role
 * @property string $url
 * @property string $ip
 * @property integer $created_at
 * @property integer $updated_at
 */
class Activity extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%podium_user_activity}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    public static function add()
    {
        try {
            $ip = Yii::$app->request->getUserIp();
            $url = Yii::$app->request->getUrl();
            
            if (empty($ip)) {
                $ip = '0.0.0.0';
            }

            if (Yii::$app->user->isGuest) {
                $result = self::_addGuest($ip, $url);
            }
            else {
                $result = self::_addUser($ip, $url);
            }
            
            if ($result) {
                return true;
            }
            else {
                Yii::trace('Cannot log user activity', __METHOD__);
                return false;
            }
        }
        catch (Exception $e) {
            Yii::trace([$e->getName(), $e->getMessage()], __METHOD__);
        }
    }
    
    protected static function _addGuest($ip, $url)
    {
        $activity = self::findOne(['ip' => $ip, 'user_id' => null]);
        if ($activity) {
            $activity->url = $url;
        }
        else {
            $activity = new Activity();
            $activity->url = $url;
            $activity->ip = $ip;
        }
        return $activity->save();
    }
    
    protected static function _addUser($ip, $url)
    {
        $activity = self::findOne(['user_id' => Yii::$app->user->id]);
        if (!$activity) {
            $activity = new Activity();
            $activity->user_id = Yii::$app->user->id;
        }
        $activity->username = Yii::$app->user->getIdentity()->getPodiumName();
        $activity->user_role = Yii::$app->user->getIdentity()->role;
        $activity->url = $url;
        $activity->ip = $ip;
        $activity->anonymous = Yii::$app->user->getIdentity()->anonymous;

        return $activity->save();
    }
    
    public static function lastActive()
    {
        $last = Cache::getInstance()->get('forum.lastactive');
        if ($last === false) {
            
            $last = [];
            $last['count'] = self::find()->where(['>', 'updated_at', time() - 15 * 60])->count();
            $last['members'] = self::find()->where(['and', ['>', 'updated_at', time() - 15 * 60], ['is not', 'user_id', null], ['anonymous' => 0]])->count();
            $last['anonymous'] = self::find()->where(['and', ['>', 'updated_at', time() - 15 * 60], ['is not', 'user_id', null], ['anonymous' => 1]])->count();
            $last['guests'] = self::find()->where(['and', ['>', 'updated_at', time() - 15 * 60], ['user_id' => null]])->count();
            $last['names'] = [];
            $members = self::find()->where(['and', ['>', 'updated_at', time() - 15 * 60], ['is not', 'user_id', null], ['anonymous' => 0]])->asArray()->all();
            foreach ($members as $member) {
                $last['names'][$member['user_id']] = [
                    'name' => $member['username'],
                    'role' => $member['user_role'],
                ];
            }
            
            Cache::getInstance()->set('forum.lastactive', $last, 60);
        }
        
        return $last;
    }
    
    public static function totalMembers()
    {
        $members = Cache::getInstance()->get('forum.memberscount');
        if ($members === false) {
            
            $members = User::find()->where(['!=', 'status', User::STATUS_REGISTERED])->count();
            Cache::getInstance()->set('forum.memberscount', $members);
        }
        
        return $members;
    }
    
    public static function totalThreads()
    {
        $threads = Cache::getInstance()->get('forum.threadscount');
        if ($threads === false) {
            
            $threads = Thread::find()->count();
            Cache::getInstance()->set('forum.threadscount', $threads);
        }
        
        return $threads;
    }
    
    public static function totalPosts()
    {
        $posts = Cache::getInstance()->get('forum.postscount');
        if ($posts === false) {
            
            $posts = Post::find()->count();
            Cache::getInstance()->set('forum.postscount', $posts);
        }
        
        return $posts;
    }
}
