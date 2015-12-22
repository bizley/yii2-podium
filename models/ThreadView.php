<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * ThreadView model
 *
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 * @property integer $id
 * @property integer $user_id
 * @property integer $thread_id
 * @property integer $new_last_seen
 * @property integer $edited_last_seen
 */
class ThreadView extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%podium_thread_view}}';
    }
    
    /**
     * Searches for threads with unread posts.
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = Thread::find()->joinWith('threadView')
                ->where([
                    'and',
                    [
                        'or', 
                        [self::tableName() . '.user_id' => User::loggedId()], 
                        [self::tableName() . '.user_id' => null]
                    ],
                    [
                        'or',
                        ['new_last_seen' => null],
                        [
                            'and',
                            ['is not', 'new_last_seen', null],
                            new Expression('`new_last_seen` < `new_post_at`')
                        ],
                        ['edited_last_seen' => null],
                        [
                            'and',
                            ['is not', 'edited_last_seen', null],
                            new Expression('`edited_last_seen` < `edited_post_at`')
                        ],
                    ],
                ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => 10,
                'forcePageParam' => false
            ],
        ]);

        $dataProvider->sort->defaultOrder = ['edited_post_at' => SORT_ASC, 'id' => SORT_ASC];
        $dataProvider->pagination->pageSize = Yii::$app->session->get('per-page', 20);

        return $dataProvider;
    }
}
