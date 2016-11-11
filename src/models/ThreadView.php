<?php

namespace bizley\podium\models;

use bizley\podium\db\ActiveRecord;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * ThreadView model
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
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
        $loggedId = User::loggedId();
        $query = Thread::find()->joinWith('threadView')->where(['or',
            ['and',
                ['user_id' => $loggedId],
                new Expression('new_last_seen < new_post_at')
            ],
            ['and',
                ['user_id' => $loggedId],
                new Expression('edited_last_seen < edited_post_at')
            ],
            ['user_id' => null],
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
