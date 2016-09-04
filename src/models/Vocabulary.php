<?php

namespace bizley\podium\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\helpers\HtmlPurifier;

/**
 * Vocabulary model
 * Automatic tag words from posts.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 * @property integer $id
 * @property string $word
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
     * @inheritdoc
     */
    public function search()
    {
        $query = static::find()->select('post_id, thread_id')->where(['like', 'word', $this->query]);
        if (Yii::$app->user->isGuest) {
            $query->joinWith(['posts' => function($q) {
                $q->joinWith(['forum'])->where([Forum::tableName() . '.visible' => 1]);
            }]);
        } else {
            $query->joinWith(['posts']);
        }
        
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
