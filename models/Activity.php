<?php

namespace bizley\podium\models;

use bizley\podium\components\Cache;
use bizley\podium\log\Log;
use Exception;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Podium Activity model
 * Members tracking and counting.
 * 
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 * 
 * @property integer $id
 * @property integer $user_id
 * @property string $username
 * @property integer $user_role
 * @property string $url
 * @property string $ip
 * @property integer $created_at
 * @property integer $updated_at
 * 
 * @property User $user
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
        return [TimestampBehavior::className()];
    }
    
    /**
     * User relation.
     * @return ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
    
    /**
     * Adds guest activity.
     * @param string $ip
     * @param string $url
     * @return boolean
     */
    protected static function _addGuest($ip, $url)
    {
        $activity = self::find()
                        ->where(['ip' => $ip, 'user_id' => null])
                        ->limit(1)
                        ->one();
        if ($activity) {
            $activity->url = $url;
        } else {
            $activity = new Activity;
            $activity->url = $url;
            $activity->ip = $ip;
        }
        return $activity->save();
    }
    
    /**
     * Adds registered user activity.
     * @param string $ip
     * @param string $url
     * @return boolean
     */
    protected static function _addUser($ip, $url)
    {
        $user = User::findMe();
        if ($user) {
            $activity = self::find()
                            ->where(['user_id' => $user->id])
                            ->limit(1)
                            ->one();
            if (!$activity) {
                $activity = new Activity;
                $activity->user_id = $user->id;
            }

            $activity->username = $user->podiumName;
            $activity->user_role = $user->role;
            $activity->user_slug = $user->podiumSlug;
            $activity->url = $url;
            $activity->ip = $ip;
            $activity->anonymous = $user->anonymous;

            return $activity->save();
        }
        return false;
    }

    /**
     * Adds user activity.
     * @return boolean
     */
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
            } else {
                $result = self::_addUser($ip, $url);
            }
            
            if ($result) {
                return true;
            } else {
                Log::error('Cannot log user activity', null, __METHOD__);
                return false;
            }
        } catch (Exception $e) {
            Log::error($e->getMessage(), null, __METHOD__);
        }
    }
    
    /**
     * Deletes user activity.
     * @param integer $id
     */
    public static function deleteUser($id)
    {
        $activity = self::find()->where(['user_id' => $id])->limit(1)->one();
        if ($activity && $activity->delete()) {
            Cache::getInstance()->delete('forum.lastactive');
        } else {
            Log::error('Cannot delete user activity', $id, __METHOD__);
        }
    }
    
    /**
     * Updates username after change.
     * @param integer $id
     * @param string $username
     * @param string $slug
     */
    public static function updateName($id, $username, $slug)
    {
        $activity = self::find()->where(['user_id' => $id])->limit(1)->one();
        if ($activity) {
            $activity->username = $username;
            $activity->user_slug = $slug;
            if ($activity->save()) {
                Cache::getInstance()->delete('forum.lastactive');
            } else {
                Log::error('Cannot update user activity', $id, __METHOD__);
            }
        } else {
            Log::error('Cannot update user activity', $id, __METHOD__);
        }
    }
    
    /**
     * Updates role after change.
     * @param integer $id
     * @param integer $role
     */
    public static function updateRole($id, $role)
    {
        $activity = self::find()->where(['user_id' => $id])->limit(1)->one();
        if ($activity) {
            $activity->role = $role;
            if ($activity->save()) {
                Cache::getInstance()->delete('forum.lastactive');
            } else {
                Log::error('Cannot update user activity', $id, __METHOD__);
            }
        } else {
            Log::error('Cannot update user activity', $id, __METHOD__);
        }
    }

    /**
     * Updates tracking.
     * @return array
     */
    public static function lastActive()
    {
        $last = Cache::getInstance()->get('forum.lastactive');
        if ($last === false) {
            $last = [
                'count'     => self::find()
                                ->where(['>', 'updated_at', time() - 15 * 60])
                                ->count(),
                'members'   => self::find()
                                ->where([
                                    'and', 
                                    ['>', 'updated_at', time() - 15 * 60], 
                                    ['is not', 'user_id', null], 
                                    ['anonymous' => 0]
                                ])
                                ->count(),
                'anonymous' => self::find()
                                ->where([
                                    'and', 
                                    ['>', 'updated_at', time() - 15 * 60], 
                                    ['is not', 'user_id', null], 
                                    ['anonymous' => 1]
                                ])
                                ->count(),
                'guests'    => self::find()
                                ->where([
                                    'and', 
                                    ['>', 'updated_at', time() - 15 * 60], 
                                    ['user_id' => null]
                                ])
                                ->count(),
                'names'     => [],
            ];
            $members = self::find()
                        ->where([
                            'and', 
                            ['>', 'updated_at', time() - 15 * 60], 
                            ['is not', 'user_id', null], 
                            ['anonymous' => 0]
                        ]);
            foreach ($members->each() as $member) {
                $last['names'][$member->user_id] = [
                    'name' => $member->username,
                    'role' => $member->user_role,
                    'slug' => $member->user_slug,
                ];
            }
            Cache::getInstance()->set('forum.lastactive', $last, 60);
        }
        return $last;
    }
    
    /**
     * Counts number of registered users.
     * @return integer
     */
    public static function totalMembers()
    {
        $members = Cache::getInstance()->get('forum.memberscount');
        if ($members === false) {
            $members = User::find()
                        ->where(['!=', 'status', User::STATUS_REGISTERED])
                        ->count();
            Cache::getInstance()->set('forum.memberscount', $members);
        }
        return $members;
    }
    
    /**
     * Counts number of created posts.
     * @return integer
     */
    public static function totalPosts()
    {
        $posts = Cache::getInstance()->get('forum.postscount');
        if ($posts === false) {
            $posts = Post::find()->count();
            Cache::getInstance()->set('forum.postscount', $posts);
        }
        return $posts;
    }
    
    /**
     * Counts number of created threads.
     * @return integer
     */
    public static function totalThreads()
    {
        $threads = Cache::getInstance()->get('forum.threadscount');
        if ($threads === false) {
            $threads = Thread::find()->count();
            Cache::getInstance()->set('forum.threadscount', $threads);
        }
        return $threads;
    }
}
