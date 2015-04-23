<?php

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
            ['content', 'string', 'min' => 10],
            ['content', 'filter', 'filter' => function($value) {
                    return HtmlPurifier::process($value, Helper::podiumPurifierConfig('full'));
                }],
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'author_id']);
    }
    
    public function getThread()
    {
        return $this->hasOne(Thread::className(), ['id' => 'thread_id']);
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
            if (!empty($formatWords)) {
                Yii::$app->db->createCommand()->batchInsert('{{%podium_vocabulary}}', ['word'], $formatWords)->execute();
            }

            $query = (new Query)->from('{{%podium_vocabulary}}')->where(['word' => $words]);
            foreach ($query->each() as $vocabularyNew) {
                $vocabulary[] = [$vocabularyNew['id'], $this->id];
            }
            if (!empty($vocabulary)) {
                Yii::$app->db->createCommand()->batchInsert('{{%podium_vocabulary_junction}}', ['word_id', 'post_id'], $vocabulary)->execute();
            }
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
            if (!empty($formatWords)) {
                Yii::$app->db->createCommand()->batchInsert('{{%podium_vocabulary}}', ['word'], $formatWords)->execute();
            }

            $query = (new Query)->from('{{%podium_vocabulary}}')->where(['word' => $words]);
            foreach ($query->each() as $vocabularyNew) {
                $vocabulary[$vocabularyNew['id']] = [$vocabularyNew['id'], $this->id];
            }
            if (!empty($vocabulary)) {
                Yii::$app->db->createCommand()->batchInsert('{{%podium_vocabulary_junction}}', ['word_id', 'post_id'], array_values($vocabulary))->execute();
            }
            
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
                'defaultPageSize' => 10,
                'pageSizeLimit' => false,
                'forcePageParam' => false
            ],
        ]);

        $dataProvider->sort->defaultOrder = ['id' => SORT_ASC];

        return $dataProvider;
    }
    
    public function markSeen()
    {
        if (!Yii::$app->user->isGuest) {
            $threadView = ThreadView::findOne(['user_id' => Yii::$app->user->id, 'thread_id' => $this->thread_id]);
            
            if (!$threadView) {
                $threadView                   = new ThreadView;
                $threadView->user_id          = Yii::$app->user->id;
                $threadView->thread_id        = $this->thread_id;
                $threadView->new_last_seen    = $this->created_at;
                $threadView->edited_last_seen = $this->edited_at;
                $threadView->save();
                $this->thread->updateCounters(['views' => 1]);
            }
            else {
                if ($this->edited) {
                    if ($threadView->edited_last_seen < $this->edited_at) {
                        $threadView->edited_last_seen = $this->edited_at;
                        $threadView->save();
                        $this->thread->updateCounters(['views' => 1]);
                    }
                }
                else {
                    $save = false;
                    if ($threadView->new_last_seen < $this->created_at) {
                        $threadView->new_last_seen = $this->created_at;
                        $save = true;
                    }
                    if ($threadView->edited_last_seen < max($this->created_at, $this->edited_at)) {
                        $threadView->edited_last_seen = max($this->created_at, $this->edited_at);
                        $save = true;
                    }
                    if ($save) {
                        $threadView->save();
                        $this->thread->updateCounters(['views' => 1]);
                    }
                }
            }
        }
    }
    
    public function isMod($user_id = null)
    {
        if (Yii::$app->user->can('admin')) {
            return true;
        }
        else {
            return false;
        }
    }
}