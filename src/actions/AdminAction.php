<?php

namespace bizley\podium\actions;

use bizley\podium\models\User;
use Yii;
use yii\base\Action;
use yii\web\Response;

/**
 * Admin Action
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.6
 */
class AdminAction extends Action
{
    /**
     * @var int current role
     */
    public $fromRole;

    /**
     * @var int target role
     */
    public $toRole;

    /**
     * @var string method name
     */
    public $method;

    /**
     * @var string
     */
    public $restrictMessage;

    /**
     * @var string
     */
    public $successMessage;

    /**
     * @var string
     */
    public $errorMessage;

    /**
     * Runs action.
     * @param int $id user ID
     * @return Response
     */
    public function run($id = null)
    {
        $model = User::find()->where(['id' => $id])->limit(1)->one();
        if (empty($model)) {
            $this->controller->error(Yii::t('podium/flash', 'Sorry! We can not find User with this ID.'));
            return $this->controller->redirect(['admin/members']);
        }
        if ($model->role != $this->fromRole) {
            $this->controller->error($this->restrictMessage);
            return $this->controller->redirect(['admin/members']);
        }
        if (call_user_func([$model, $this->method], $this->toRole)) {
            $this->controller->success($this->successMessage);
            if ($this->method == 'promoteTo') {
                return $this->controller->redirect(['admin/mods', 'id' => $model->id]);
            }
            return $this->controller->redirect(['admin/members']);
        }
        $this->controller->error($this->errorMessage);
        return $this->controller->redirect(['admin/members']);
    }
}
