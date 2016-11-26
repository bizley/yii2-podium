<?php

namespace bizley\podium\models;

use bizley\podium\db\ActiveRecord;
use bizley\podium\db\Query;
use bizley\podium\log\Log;
use bizley\podium\Podium;
use Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * Poll model
 * Forum polls.
 * 
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.5
 * 
 * @property int $id
 * @property string $question
 * @property int $votes
 * @property int $hidden
 * @property int $end_at
 * @property int $thread_id
 * @property int $author_id
 * @property int $create_at
 * @property int $updated_at
 * 
 * @property array $currentVotes
 * @property int $votesCount
 * @property Thread $thread
 * @property PollAnswer[] $sortedAnswers
 * @property PollAnswer[] $answers
 * @property User $author
 */
class Poll extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%podium_poll}}';
    }
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [TimestampBehavior::className()];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['question', 'votes', 'hidden', 'thread_id', 'author_id'], 'required'],
            ['question', 'string', 'max' => 255],
            ['votes', 'integer', 'min' => 1],
            ['end_at', 'integer'],
            ['hidden', 'boolean'],
        ];
    }
    
    /**
     * Thread relation.
     * @return ActiveQuery
     */
    public function getThread()
    {
        return $this->hasOne(Thread::className(), ['id' => 'thread_id']);
    }
    
    /**
     * Author relation.
     * @return ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(User::className(), ['id' => 'author_id']);
    }
    
    /**
     * Answers relation.
     * @return ActiveQuery
     */
    public function getAnswers()
    {
        return $this->hasMany(PollAnswer::className(), ['poll_id' => 'id']);
    }
    
    /**
     * Sorted answers.
     * @return PollAnswer[]
     */
    public function getSortedAnswers()
    {
        return $this->getAnswers()->orderBy(['votes' => SORT_DESC])->all();
    }
    
    /**
     * Checks if user has already voted in poll.
     * @param int $user_id
     * @return bool
     */
    public function getUserVoted($user_id)
    {
        return (new Query())->from('{{%podium_poll_vote}}')->where([
            'poll_id' => $this->id,
            'caster_id' => $user_id
        ])->count('id') ? true : false;
    }
    
    /**
     * Votes in poll.
     * @param int $user_id
     * @param array $answers
     * @return bool
     */
    public function vote($user_id, $answers)
    {
        $votes = [];
        $time = time();
        foreach ($answers as $answer) {
            $votes[] = [$this->id, $answer, $user_id, $time];
        }
        if (!empty($votes)) {
            Podium::getInstance()->db->createCommand()->batchInsert('{{%podium_poll_vote}}', ['poll_id', 'answer_id', 'caster_id', 'created_at'], $votes)->execute();
            PollAnswer::updateAllCounters(['votes' => 1], ['id' => $answers]);
            return true;
        }
        return false;
    }
    
    /**
     * Checks if poll has given answer.
     * @param int $answer_id
     * @return bool
     */
    public function hasAnswer($answer_id)
    {
        foreach ($this->answers as $answer) {
            if ($answer->id == $answer_id) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Returns list of answers with the current number of votes.
     * @return array
     */
    public function getCurrentVotes()
    {
        $this->refresh();
        return ArrayHelper::map($this->answers, 'id', 'votes');
    }
    
    /**
     * Returns number of casted votes.
     * @return int
     */
    public function getVotesCount()
    {
        $votes = 0;
        foreach ($this->answers as $answer) {
            $votes += $answer->votes;
        }
        return $votes;
    }
    
    /**
     * Performs poll delete with answers and votes.
     * @return bool
     */
    public function podiumDelete()
    {
        $transaction = static::getDb()->beginTransaction();
        try {
            Podium::getInstance()->db->createCommand()->delete('{{%podium_poll_vote}}', ['poll_id' => $this->id])->execute();
            if (!PollAnswer::deleteAll(['poll_id' => $this->id])) {
                throw new Exception('Poll Answers deleting error!');
            }
            if (!$this->delete()) {
                throw new Exception('Poll deleting error!');
            }
            $transaction->commit();
            Log::info('Poll deleted', !empty($this->id) ? $this->id : '', __METHOD__);
            return true;
        } catch (Exception $e) {
            $transaction->rollBack();
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }
}
