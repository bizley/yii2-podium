<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\controllers;

use bizley\podium\behaviors\FlashBehavior;
use bizley\podium\components\Config;
use bizley\podium\components\Log;
use bizley\podium\models\Email;
use bizley\podium\models\Meta;
use bizley\podium\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\UploadedFile;

/**
 * Podium Profile controller
 * All actions concerning member profile.
 * 
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */
class ProfileController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class'        => AccessControl::className(),
                'denyCallback' => function () {
                    return $this->redirect(['account/login']);
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
    
    /**
     * Updating the profile details.
     * @return string|\yii\web\Response
     */
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
                        
                        if (Email::queue($model->getUser()->email, 
                                Yii::t('podium/mail', 'New e-mail activation link at {name}', ['name' => Config::getInstance()->get('name')]),
                                $this->renderPartial('/mail/new_email', [
                                        'forum' => Config::getInstance()->get('name'),
                                        'link'  => Url::to(['account/new-email',
                                        'token' => $model->email_token], true)
                                    ]),
                                !empty($model->id) ? $model->id : null
                            )) {
                            Log::info('New email activation link queued', !empty($model->id) ? $model->id : '', __METHOD__);
                            $this->success('Your account has been updated but your new e-mail address is not active yet. '
                                    . 'Click the activation link that has been sent to your new e-mail address.');
                        }
                        else {
                            Log::error('Error while queuing new email activation link', !empty($model->id) ? $model->id : '', __METHOD__);
                            $this->warning('Your account has been updated but your new e-mail address is not active yet. '
                                    . 'Unfortunately there was some error while sending you the activation link. '
                                    . 'Contact administrator about this problem.');
                        }
                    }
                    else {
                        Log::info('Details updated', !empty($model->id) ? $model->id : '', __METHOD__);
                        $this->success('Your account has been updated.');
                    }

                    return $this->refresh();
                }
            }
            else {
                $model->current_password = null;
            }
        }

        return $this->render('details', ['model' => $model]);
    }
    
    /**
     * Updating the forum details.
     * @return string|\yii\web\Response
     */
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
                        Log::error('Error while creating avatars folder', null, __METHOD__);
                        $this->error('Sorry! There was an error while creating the avatars folder. Contact administrator about this problem.');
                    }
                }
                if ($folderExists) {
                    if (!empty($model->avatar)) {
                        if (!unlink($path . DIRECTORY_SEPARATOR . $model->avatar)) {
                            Log::error('Error while deleting old avatar image', null, __METHOD__);
                        }
                    }
                    $model->avatar = Yii::$app->security->generateRandomString() . '.' . $avatar->getExtension();
                    $uploadAvatar = true;
                }
            }
            
            if ($model->save()) {
                if ($uploadAvatar) {
                    if (!$avatar->saveAs($path . DIRECTORY_SEPARATOR . $model->avatar)) {
                        Log::error('Error while saving avatar image', null, __METHOD__);
                        $this->error('Sorry! There was an error while uploading the avatar image. Contact administrator about this problem.');
                    }
                }
                Log::info('Profile updated', !empty($model->id) ? $model->id : '', __METHOD__);
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

    /**
     * Showing the profile card.
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
        $model = User::findOne(Yii::$app->user->id);

        if (empty($model)) {
            return $this->redirect(['account/login']);
        }

        return $this->render('profile', ['model' => $model]);
    }
    
    /**
     * Signing out.
     * @return \yii\web\Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->module->goPodium();
    }
}                