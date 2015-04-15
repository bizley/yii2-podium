<?php

/**
 * @author Bizley
 */
namespace bizley\podium\models;

use bizley\podium\components\Helper;
use Exception;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\HtmlPurifier;

/**
 * Post model
 *
 * @property integer $id
 * @property string $content
 * @property integer $thread_id
 * @property integer $forum_id
 * @property integer $author_id
 * @property integer $likes
 * @property integer $dislikes
 * @property integer $updated_at
 * @property integer $created_at
 */
class Post extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%podium_post}}';
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

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['content', 'required'],
            ['content', 'filter', 'filter' => function($value) {
                    return HtmlPurifier::process($value, Helper::podiumPurifierConfig('full'));
                }],
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'author_id']);
    }
    
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        try {
            if ($insert) {
                $this->_insertWords();
            }
            else {
                $this->_updateWords();
            }
        }
        catch (Exception $e) {
            throw $e;
        }
    }

    protected function _insertWords()
    {
        try {
            $wordsRaw = array_unique(explode(' ', preg_replace('/\s/', ' ', strip_tags(str_replace(["\n", '<br>', '<br />'], ' ', $this->content)))));
            $words    = [];
            $vocabulary = [];
            foreach ($wordsRaw as $word) {
                if (mb_strlen($word, 'UTF-8') > 2 && mb_strlen($word, 'UTF-8') <= 255) {
                    $words[] = $word;
                }
            }

            $query = (new Query)->from('{{%podium_vocabulary}}')->where(['word' => $words]);
            foreach ($query->each() as $vocabularyFound) {
                if (($key = array_search($vocabularyFound['word'], $words)) !== false) {
                    unset($words[$key]);
                }
            }
            $formatWords = [];
            foreach ($words as $word) {
                $formatWords[] = [$word];
            }
            Yii::$app->db->createCommand()->batchInsert('{{%podium_vocabulary}}', ['word'], $formatWords)->execute();

            $query = (new Query)->from('{{%podium_vocabulary}}')->where(['word' => $words]);
            foreach ($query->each() as $vocabularyNew) {
                $vocabulary[] = [$vocabularyNew['id'], $this->id];
            }
            Yii::$app->db->createCommand()->batchInsert('{{%podium_vocabulary_junction}}', ['word_id', 'post_id'], $vocabulary)->execute();
        }
        catch (Exception $e) {
            throw $e;
        }
    }

    protected function _updateWords()
    {
        try {
            $wordsRaw = array_unique(explode(' ', preg_replace('/\s/', ' ', strip_tags(str_replace(["\n", '<br>', '<br />'], ' ', $this->content)))));
            $words    = [];
            $vocabulary = [];
            foreach ($wordsRaw as $word) {
                if (mb_strlen($word, 'UTF-8') > 2 && mb_strlen($word, 'UTF-8') <= 255) {
                    $words[] = $word;
                }
            }

            $query = (new Query)->from('{{%podium_vocabulary}}')->where(['word' => $words]);
            foreach ($query->each() as $vocabularyFound) {
                if (($key = array_search($vocabularyFound['word'], $words)) !== false) {
                    unset($words[$key]);
                }
            }
            
            $formatWords = [];
            foreach ($words as $word) {
                $formatWords[] = [$word];
            }
            Yii::$app->db->createCommand()->batchInsert('{{%podium_vocabulary}}', ['word'], $formatWords)->execute();

            $query = (new Query)->from('{{%podium_vocabulary}}')->where(['word' => $words]);
            foreach ($query->each() as $vocabularyNew) {
                $vocabulary[$vocabularyNew['id']] = [$vocabularyNew['id'], $this->id];
            }
            Yii::$app->db->createCommand()->batchInsert('{{%podium_vocabulary_junction}}', ['word_id', 'post_id'], array_values($vocabulary))->execute();
            
            $query = (new Query)->from('{{%podium_vocabulary_junction}}')->where(['post_id' => $this->id]);
            foreach ($query->each() as $junk) {
                if (!array_key_exists($junk['word_id'], $vocabulary)) {
                    Yii::$app->db->createCommand()->delete('{{%podium_vocabulary_junction}}', ['id' => $junk['id']])->execute();
                }
            }
        }
        catch (Exception $e) {
            throw $e;
        }
    }
    
    public function search($forum_id, $thread_id)
    {
        $query = self::find()->where(['forum_id' => $forum_id, 'thread_id' => $thread_id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        $dataProvider->sort->defaultOrder = ['id' => SORT_ASC];

        return $dataProvider;
    }
}