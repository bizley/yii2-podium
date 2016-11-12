<?php

namespace bizley\podium\models;

use bizley\podium\db\ActiveRecord;
use bizley\podium\Podium;
use yii\data\ActiveDataProvider;
use yii\helpers\HtmlPurifier;

/**
 * Vocabulary model
 * Automatic tag words from posts.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */
class Vocabulary extends ActiveRecord
{
    /**
     * @var string Query
     */
    public $query;
    
    /**
     * @var int 
     */
    public $thread_id;
    
    /**
     * @var int 
     */
    public $post_id;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%podium_vocabulary}}';
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['query', 'string'],
            ['query', 'filter', 'filter' => function($value) {
                return HtmlPurifier::process($value);
            }],
        ];
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
     * Post relation.
     * @return Post
     */
    public function getPostData()
    {
        return $this->hasOne(Post::className(), ['id' => 'post_id']);
    }

    /**
     * Posts relation via junction.
     * @return Post[]
     */
    public function getPosts()
    {
        return $this->hasMany(Post::className(), ['id' => 'post_id'])->viaTable('{{%podium_vocabulary_junction}}', ['word_id' => 'id']);
    }
    
    /**
     * Returns data provider for simple search.
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = static::find()->where(['and',
            ['is not', 'post_id', null],
            ['like', 'word', $this->query]
        ])->joinWith(['posts.author', 'posts.thread']);
        if (Podium::getInstance()->user->isGuest) {
            $query->joinWith(['posts.forum' => function($q) {
                $q->where([Forum::tableName() . '.visible' => 1]);
            }]);
        }
        $query->groupBy(['post_id']);
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['thread_id' => SORT_DESC],
                'attributes' => [
                    'thread_id' => [
                        'asc' => ['thread_id' => SORT_ASC],
                        'desc' => ['thread_id' => SORT_DESC],
                        'default' => SORT_DESC,
                    ],
                ]
            ],
        ]);

        return $dataProvider;
    }
}
