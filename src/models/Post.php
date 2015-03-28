<?php

/**
 * @author Bizley
 */
namespace bizley\podium\models;

use bizley\podium\components\Helper;
use Exception;
use Yii;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\db\QueryBuilder;
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
            SluggableBehavior::className(),
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
                    return HtmlPurifier::process($value, Helper::podiumPurifier());
                }],
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert) {
            $this->_insertWords();
        }
        else {
            $this->_updateWords();
        }
    }

    protected function _insertWords()
    {
        try {
            $wordsRaw = array_unique(explode(' ', preg_replace('\s', ' ', strip_tags($this->content))));
            $words    = [];
            $vocabulary = [];
            foreach ($wordsRaw as $word) {
                if (mb_strlen($word, 'UTF-8') > 3) {
                    $words[] = $word;
                }
            }

            $queryBuilder = new QueryBuilder(Yii::$app->db);
            
            $query = new Query();
            $query->from('{{%podium_vocabulary}}')->where(['word' => $words]);
            foreach ($query->each() as $word) {
                $vocabulary[] = [$word->id, $this->id];
                if (($key = array_search($word->word, $words)) !== false) {
                    unset($words[$key]);
                }
            }
            Yii::$app->db->createCommand($queryBuilder->batchInsert('{{%podium_vocabulary}}', ['word'], $words))->execute();

            $query = new Query();
            $query->from('{{%podium_vocabulary}}')->where(['word' => $words]);
            foreach ($query->each() as $word) {
                $vocabulary[] = [$word->id, $this->id];
            }
            Yii::$app->db->createCommand($queryBuilder->batchInsert('{{%podium_vocabulary_junction}}', ['word_id',
                'post_id'], $vocabulary))->execute();
        }
        catch (Exception $e) {
            Yii::trace([$e->getName(), $e->getMessage()], __METHOD__);
        }
    }

    protected function _updateWords()
    {
        try {
            $wordsRaw = array_unique(explode(' ', preg_replace('\s', ' ', strip_tags($this->content))));
            $words    = [];
            $vocabulary = [];
            foreach ($wordsRaw as $word) {
                if (mb_strlen($word, 'UTF-8') > 3) {
                    $words[] = $word;
                }
            }

            $queryBuilder = new QueryBuilder(Yii::$app->db);
            
            $query = new Query();
            $query->from('{{%podium_vocabulary}}')->where(['word' => $words]);
            foreach ($query->each() as $word) {
                $vocabulary[$word->id] = [$word->id, $this->id];
                if (($key = array_search($word->word, $words)) !== false) {
                    unset($words[$key]);
                }
            }
            Yii::$app->db->createCommand($queryBuilder->batchInsert('{{%podium_vocabulary}}', ['word'], $words))->execute();

            $query = new Query();
            $query->from('{{%podium_vocabulary}}')->where(['word' => $words]);
            foreach ($query->each() as $word) {
                $vocabulary[$word->id] = [$word->id, $this->id];
            }
            Yii::$app->db->createCommand($queryBuilder->batchInsert('{{%podium_vocabulary_junction}}', ['word_id',
                'post_id'], array_values($vocabulary)))->execute();
            
            $query = new Query();
            $query->from('{{%podium_vocabulary_junction}}')->where(['post_id' => $this->id]);
            foreach ($query->each() as $junk) {
                if (!array_key_exists($junk->word_id, $vocabulary)) {
                    Yii::$app->db->createCommand($queryBuilder->delete('{{%podium_vocabulary_junction}}', ['id' => $junk->id]))->execute();
                }
            }
        }
        catch (Exception $e) {
            Yii::trace([$e->getName(), $e->getMessage()], __METHOD__);
        }
    }
}