<?php

namespace bizley\podium\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\helpers\Url;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;
use bizley\podium\models\User;
use bizley\podium\models\Meta;
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

        return $this->module->goPodium();
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
        $model = Meta::findOne(['user_id' => Yii::$app->user->id]);
        
        if (empty($model)) {
            $model = new Meta();
        }
        
        if ($model->load(Yii::$app->request->post())) {
            $model->user_id = Yii::$app->user->id;
            $uploadAvatar = false;
            $path = Yii::getAlias('@webroot/avatars');
            $avatar = UploadedFile::getInstance($model, 'image');
            if ($avatar) {
                $folderExists = true;
                if (!file_exists($path)) {
                    if (!FileHelper::createDirectory($path)) {
                        $folderExists = false;
                        $this->error('Sorry! There was an error while creating the avatars folder. Contact administrator about this problem.');
                    }
                }
                if ($folderExists) {
                    if (!empty($model->avatar)) {
                        if (!unlink($path . DIRECTORY_SEPARATOR . $model->avatar)) {
                            // TODO: log error
                        }
                    }
                    $model->avatar = Yii::$app->security->generateRandomString() . '.' . $avatar->getExtension();
                    $uploadAvatar = true;
                }
            }
            
            if ($model->save()) {
                if ($uploadAvatar) {
                    if (!$avatar->saveAs($path . DIRECTORY_SEPARATOR . $model->avatar)) {
                        $this->error('Sorry! There was an error while uploading the avatar image. Contact administrator about this problem.');
                    }
                }
                $this->success('Your profile details have been updated.');
                return $this->refresh();
            }
            else {
                $model->current_password = null;
            }
        }

        return $this->render('forum', [
                'model' => $model,
                'user' => User::findOne(Yii::$app->user->id)
        ]);
    }
}
                