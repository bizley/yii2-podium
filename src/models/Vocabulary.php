<?php

/**
 * @author Bizley
 */
namespace bizley\podium\models;

use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\helpers\HtmlPurifier;

/**
 * Vocabulary model
 *
 * @property integer $id
 * @property string $word
 */
class Vocabulary extends ActiveRecord
{

    public $query;
    public $thread_id;
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
    
    public function getThread()
    {
        return $this->hasOne(Thread::className(), ['id' => 'thread_id']);
    }
    
    public function getPost()
    {
        return $this->hasOne(Post::className(), ['id' => 'post_id']);
    }

    public function getPosts()
    {
        return $this->hasMany(Post::className(), ['id' => 'post_id'])
            ->viaTable('{{%podium_vocabulary_junction}}', ['word_id' => 'id']);
    }
    
    public function search()
    {
        $query = self::find()->select('post_id, thread_id')->joinWith(['posts'])->where(['like', 'word', $this->query]);
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['thread_id' => SORT_DESC],
                'attributes' => [
                    'thread_id' => [
                        'asc'     => ['thread_id' => SORT_ASC],
                        'desc'    => ['thread_id' => SORT_DESC],
                        'default' => SORT_DESC,
                    ],
                ]
            ],
        ]);

        return $dataProvider;
    }
}
