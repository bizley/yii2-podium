<?php

namespace bizley\podium\models;

use bizley\podium\db\Query;
use bizley\podium\log\Log;
use bizley\podium\models\db\PollActiveRecord;
use bizley\podium\Podium;
use Exception;
use yii\helpers\ArrayHelper;

/**
 * Poll model
 * Forum polls.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.5
 *
 * @property array $currentVotes
 * @property int $votesCount
 * @property PollAnswer[] $sortedAnswers
 */
class Poll extends PollActiveRecord
{
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
     * @param int $userId
     * @return bool
     */
    public function getUserVoted($userId)
    {
        return (new Query())->from('{{%podium_poll_vote}}')->where([
            'poll_id' => $this->id,
            'caster_id' => $userId
        ])->count('id') ? true : false;
    }

    /**
     * Votes in poll.
     * @param int $userId
     * @param array $answers
     * @return bool
     */
    public function vote($userId, $answers)
    {
        $votes = [];
        $time = time();
        foreach ($answers as $answer) {
            $votes[] = [$this->id, $answer, $userId, $time];
        }
        if (!empty($votes)) {
            $transaction = static::getDb()->beginTransaction();
            try {
                if (!Podium::getInstance()->db->createCommand()->batchInsert(
                        '{{%podium_poll_vote}}', ['poll_id', 'answer_id', 'caster_id', 'created_at'], $votes
                    )->execute()) {
                    throw new Exception('Votes saving error!');
                }
                if (!PollAnswer::updateAllCounters(['votes' => 1], ['id' => $answers])) {
                    throw new Exception('Votes adding error!');
                }
                $transaction->commit();
                return true;
            } catch (Exception $e) {
                $transaction->rollBack();
                Log::error($e->getMessage(), $this->id, __METHOD__);
            }
        }
        return false;
    }

    /**
     * Checks if poll has given answer.
     * @param int $answerId
     * @return bool
     */
    public function hasAnswer($answerId)
    {
        foreach ($this->answers as $answer) {
            if ($answer->id == $answerId) {
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
            if (!Podium::getInstance()->db->createCommand()->delete('{{%podium_poll_vote}}', ['poll_id' => $this->id])->execute()) {
                throw new Exception('Poll Votes deleting error!');
            }
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

    /**
     * Performs poll update.
     * @return bool
     */
    public function podiumEdit()
    {
        $transaction = static::getDb()->beginTransaction();
        try {
            if (!$this->save()) {
                throw new Exception('Poll saving error!');
            }

            foreach ($this->editAnswers as $answer) {
                foreach ($this->answers as $oldAnswer) {
                    if ($answer == $oldAnswer->answer) {
                        continue(2);
                    }
                }
                $pollAnswer = new PollAnswer();
                $pollAnswer->poll_id = $this->id;
                $pollAnswer->answer = $answer;
                if (!$pollAnswer->save()) {
                    throw new Exception('Poll Answer saving error!');
                }
            }
            foreach ($this->answers as $oldAnswer) {
                foreach ($this->editAnswers as $answer) {
                    if ($answer == $oldAnswer->answer) {
                        continue(2);
                    }
                }
                if (!$oldAnswer->delete()) {
                    throw new Exception('Poll Answer deleting error!');
                }
            }

            $transaction->commit();
            Log::info('Poll updated', $this->id, __METHOD__);
            return true;
        } catch (Exception $e) {
            $transaction->rollBack();
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }
}
