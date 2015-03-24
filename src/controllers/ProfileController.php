<?php

namespace bizley\podium\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\helpers\Url;
use bizley\podium\models\User;
use bizley\podium\models\UserMeta;
use bizley\podium\behaviors\FlashBehavior;

class ProfileController extends Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class'        => AccessControl::className(),
                'denyCallback' => function () {
                    return $this->redirect(['login']);
                },
                'rules'  => [
                    [
                        'allow'         => false,
                        'matchCallback' => function () {
                            return !$this->module->getInstalled();
                        },
                        'denyCallback' => function () {
                            return $this->redirect(['install/run']);
                        }
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'flash' => FlashBehavior::className(),
        ];
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionIndex()
    {
        $model = User::findOne(Yii::$app->user->id);

        if (empty($model)) {
            return $this->redirect(['account/login']);
        }

        return $this->render('profile', [
                    'model' => $model
        ]);
    }

    public function actionDetails()
    {
        $model = User::findOne(Yii::$app->user->id);

        if (empty($model)) {
            return $this->redirect(['account/login']);
        }

        $model->setScenario('account');

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                if ($model->saveChanges()) {
                    if ($model->new_email) {
                        try {
                            $mailer = Yii::$app->mailer->compose('/mail/new_email', [
                                        'forum' => $this->module->getParam('name', 'Podium Forum'),
                                        'link'  => Url::to(['account/new-email',
                                            'token' => $model->email_token], true)
                                    ])->setFrom($this->module->getParam('email', 'no-reply@podium-default.net'))
                                    ->setTo($model->new_email)
                                    ->setSubject(Yii::t('podium/mail', 'New e-mail activation link at {NAME}', ['NAME' => $this->module->getParam('name', 'Podium Forum')]));
                            if ($mailer->send()) {
                                $this->success('Your account has been updated but your new e-mail address is not active yet. Click the activation link that has been sent to your new e-mail address.');
                            }
                            else {
                                $this->warning('Your account has been updated but your new e-mail address is not active yet. '
                                        . 'Unfortunately there was some error while sending you the activation link. '
                                        . 'Contact administrator about this problem.');
                            }
                        } catch (\Exception $e) {
                            $this->warning('Your account has been updated but your new e-mail address is not active yet. '
                                    . 'Unfortunately there was some error while sending you the activation link. '
                                    . 'Contact administrator about this problem.');
                        }
                    }
                    else {
                        $this->success('Your account has been updated.');
                    }

                    return $this->refresh();
                }
            }
            else {
                $model->current_password = null;
            }
        }

        return $this->render('details', [
                    'model' => $model
        ]);
    }

    public function actionForum()
    {
        $model = UserMeta::findOne(Yii::$app->user->id);

        if (empty($model)) {
            return $this->redirect(['account/login']);
        }

        return $this->render('forum', [
                    'model' => $model
        ]);
    }
}
                