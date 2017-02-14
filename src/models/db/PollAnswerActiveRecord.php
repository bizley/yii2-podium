<?php

namespace bizley\podium\models\db;

use bizley\podium\db\ActiveRecord;

/**
 * Poll answer model
 * Forum polls.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.6
 *
 * @property int $id
 * @property string $answer
 * @property int $votes
 * @property int $poll_id
 */
class PollAnswerActiveRecord extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%podium_poll_answer}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['answer', 'poll_id'], 'required'],
            ['answer', 'string', 'max' => 255],
            ['votes', 'default', 'value' => 0],
            ['votes', 'integer', 'min' => 0],
            ['poll_id', 'integer'],
        ];
    }
}
