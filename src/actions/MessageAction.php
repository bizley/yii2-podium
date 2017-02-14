<?php

namespace bizley\podium\actions;

use bizley\podium\log\Log;
use bizley\podium\models\Message;
use bizley\podium\models\MessageReceiver;
use bizley\podium\models\User;
use Yii;
use yii\base\Action;
use yii\db\ActiveQuery;
use yii\web\Response;

/**
 * Message Action
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.6
 */
class MessageAction extends Action
{
    /**
     * @var array
     */
    public $redirectRoute;

    /**
     * @var string sender or receiver
     */
    public $type;

    /**
     * Returns deleted status based on type.
     * @return int
     */
    public function getDeletedStatus()
    {
        if ($this->type == 'sender') {
            return Message::STATUS_DELETED;
        }
        return MessageReceiver::STATUS_DELETED;
    }

    /**
     * Returns model query based on type.
     * @return ActiveQuery
     */
    public function getModelQuery()
    {
        if ($this->type == 'sender') {
            return Message::find();
        }
        return MessageReceiver::find();
    }

    /**
     * Runs action.
     * @param int $id
     * @return Response
     */
    public function run($id = null)
    {
        if (!is_numeric($id) || $id < 1) {
            $this->controller->error(Yii::t('podium/flash', 'Sorry! We can not find the message you are looking for.'));
            return $this->controller->redirect($this->redirectRoute);
        }
        $model = $this->modelQuery->where([
                'and',
                ['id' => $id, $this->type . '_id' => User::loggedId()],
                ['!=', $this->type . '_status', $this->deletedStatus]
            ])->limit(1)->one();
        if (empty($model)) {
            $this->controller->error(Yii::t('podium/flash', 'Sorry! We can not find the message with the given ID.'));
            return $this->controller->redirect($this->redirectRoute);
        }
        if ($model->remove()) {
            $this->controller->success(Yii::t('podium/flash', 'Message has been deleted.'));
        } else {
            Log::error('Error while deleting message', $model->id, __METHOD__);
            $this->controller->error(Yii::t('podium/flash', 'Sorry! We can not delete this message. Contact administrator about this problem.'));
        }
        return $this->controller->redirect($this->redirectRoute);
    }
}
