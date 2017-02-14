<?php

namespace bizley\podium\actions;

use bizley\podium\models\User;
use Yii;
use yii\base\Action;
use yii\web\Response;

/**
 * Member Action
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.6
 */
class MemberAction extends Action
{
    /**
     * @var string view name
     */
    public $view;

    /**
     * Runs action.
     * @param int $id user ID
     * @param string $slug user slug
     * @return string|Response
     */
    public function run($id = null, $slug = null)
    {
        if (!is_numeric($id) || $id < 1 || empty($slug)) {
            $this->controller->error(Yii::t('podium/flash', 'Sorry! We can not find the user you are looking for.'));
            return $this->controller->redirect(['members/index']);
        }

        $user = User::find()->where(['and',
            ['id' => $id],
            ['or',
                ['slug' => $slug],
                ['slug' => ''],
                ['slug' => null],
            ]
        ])->limit(1)->one();
        if (empty($user)) {
            $this->controller->error(Yii::t('podium/flash', 'Sorry! We can not find the user you are looking for.'));
            return $this->controller->redirect(['members/index']);
        }
        return $this->controller->render($this->view, ['user' => $user]);
    }
}
