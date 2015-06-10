<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\controllers;

use bizley\podium\behaviors\FlashBehavior;
use bizley\podium\components\Cache;
use bizley\podium\components\Log;
use bizley\podium\models\LoginForm;
use bizley\podium\models\ReForm;
use bizley\podium\models\User;
use Exception;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Controller;

/**
 * Podium Account controller
 * All actions concerning user account.
 * 
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */
class AccountController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'denyCallback' => function () {
                    return $this->module->goPodium();
                },
                'rules' => [
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
                        'actions' => ['new-email']
                    ],
                    [
                        'allow' => true,
                        'roles' => ['?']
                    ],
                ],
            ],
            'flash' => FlashBehavior::className(),
        ];
    }
    
    /**
     * Activating the account based on the provided activation token.
     * @param string $token
     * @return \yii\web\Response
     */
    public function actionActivate($token)
    {
        $model = User::findByActivationToken($token);

        if ($model) {
            $model->setScenario('token');
            if ($model->activate()) {
                Cache::getInstance()->delete('members.fieldlist');
                Cache::getInstance()->delete('forum.memberscount');
                Log::info('Account activated', !empty($model->id) ? $model->id : '', __METHOD__);
                $this->success('Your account has been activated. You can sign in now.');
            }
            else {
                Log::error('Error while activating account', !empty($model->id) ? $model->id : '', __METHOD__);
                $this->error('Sorry! There was some error while activating your account. Contact administrator about this problem.');
            }
            return $this->module->goPodium();
        }
        else {
            $this->error('The provided activation token is invalid or expired.');
            return $this->module->goPodium();
        }
    }

    /**
     * Signing in.
     * @return string|\yii\web\Response
     */
    public function actionLogin()
    {
        $model = new LoginForm();

        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->module->goPodium();
        }
        else {
            return $this->render('login', [
                        'model' => $model,
            ]);
        }
    }
    
    /**
     * Activating new email address based on the provided token.
     * @param string $token
     * @return \yii\web\Response
     */
    public function actionNewEmail($token)
    {
        $model = User::findByEmailToken($token);

        if ($model) {
            $model->setScenario('token');
            if ($model->changeEmail()) {
                Log::info('Email address changed', !empty($model->id) ? $model->id : '', __METHOD__);
                $this->success('Your new e-mail address has been activated.');
            }
            else {
                Log::error('Error while activating email', !empty($model->id) ? $model->id : '', __METHOD__);
                $this->error('Sorry! There was some error while activating your new e-mail address. Contact administrator about this problem.');
            }
            return $this->module->goPodium();
        }
        else {
            $this->error('The provided activation token is invalid or expired.');
            return $this->module->goPodium();
        }
    }

    /**
     * Changing the account password with provided token.
     * @param string $token
     * @return string|\yii\web\Response
     */
    public function actionPassword($token)
    {
        $model = User::findByPasswordResetToken($token);

        if ($model) {
            $model->setScenario('passwordChange');
            if ($model->load(Yii::$app->request->post()) && $model->changePassword()) {
                Log::info('Password changed', !empty($model->id) ? $model->id : '', __METHOD__);
                $this->success('Your account password has been changed.');
                return $this->module->goPodium();
            }
            else {
                return $this->render('password', [
                        'model' => $model
                ]);
            }
        }
        else {
            $this->error('The provided password reset token is invalid or expired.');
            return $this->module->goPodium();
        }
    }
    
    /**
     * Resending the account activation link.
     * @return string|\yii\web\Response
     */
    public function actionReactivate()
    {
        $model = new ReForm();

        if ($model->load(Yii::$app->request->post())) {

            if ($model->reactivate()) {
                try {
                    $mailer = Yii::$app->mailer->compose('/mail/reactivate', [
                                'forum' => $this->module->getParam('name', 'Podium Forum'),
                                'link'  => Url::to(['account/activate', 'token' => $model->getUser()->activation_token], true)
                            ])->setFrom($this->module->getParam('email', 'no-reply@podium-default.net'))
                            ->setTo($model->getUser()->email)
                            ->setSubject(Yii::t('podium/mail', '{NAME} password reset link', ['NAME' => $this->module->getParam('name', 'Podium Forum')]));
                    if ($mailer->send()) {
                        Log::info('Reactivation link sent', !empty($model->id) ? $model->id : '', __METHOD__);
                        $this->success('The account activation link has been sent to your e-mail address.');
                    }
                    else {
                        Log::error('Mailer error while sending reactivation link', !empty($model->id) ? $model->id : '', __METHOD__);
                        $this->error('Sorry! There was some error while sending you the account activation link. Contact administrator about this problem.');
                    }

                    return $this->module->goPodium();
                }
                catch (Exception $e) {
                    Log::error('Error while sending reactivation link', !empty($model->id) ? $model->id : '', __METHOD__);
                    $this->error('Sorry! There was some error while sending you the account activation link. Contact administrator about this problem.');
                }
            }
            else {
                $this->error('Sorry! We can not find the account with that user name or e-mail address.');
            }
        }

        return $this->render('reactivate', [
                    'model' => $model,
        ]);
    }
    
    /**
     * Registering the new account and sending the activation link.
     * @return string|\yii\web\Response
     */
    public function actionRegister()
    {
        $model = new User();
        $model->setScenario('register');

        if ($model->load(Yii::$app->request->post()) && $model->register()) {
            try {
                $mailer = Yii::$app->mailer->compose('/mail/register', [
                            'forum' => $this->module->getParam('name', 'Podium Forum'),
                            'link'  => Url::to(['account/activate', 'token' => $model->activation_token], true)
                        ])->setFrom($this->module->getParam('email', 'no-reply@podium-default.net'))
                        ->setTo($model->email)
                        ->setSubject(Yii::t('podium/mail', 'Welcome to {NAME}! This is your activation link', ['NAME' => $this->module->getParam('name', 'Podium Forum')]));
                if ($mailer->send()) {
                    Log::info('Activation link sent', !empty($model->id) ? $model->id : '', __METHOD__);
                    $this->success('Your account has been created but it is not active yet. Click the activation link that has been sent to your e-mail address.');
                }
                else {
                    Log::warning('Mailer error while sending activation link', !empty($model->id) ? $model->id : '', __METHOD__);
                    $this->warning('Your account has been created but it is not active yet. '
                            . 'Unfortunately there was some error while sending you the activation link. '
                            . 'Contact administrator about this or try to {LINK}resend the link{CLOSELINK}.', [
                        'LINK'      => Html::beginTag('a', ['href' => Url::to('account/reactivate')]),
                        'CLOSELINK' => Html::endTag('a')
                    ]);
                }

                return $this->module->goPodium();
            }
            catch (Exception $e) {
                Log::warning('Error while sending activation link', !empty($model->id) ? $model->id : '', __METHOD__);
                $this->warning('Your account has been created but it is not active yet. '
                        . 'Unfortunately there was some error while sending you the activation link. '
                        . 'Contact administrator about this or try to {LINK}resend the link{CLOSELINK}.', [
                    'LINK'      => Html::beginTag('a', ['href' => Url::to('account/reactivate')]),
                    'CLOSELINK' => Html::endTag('a')
                ]);
            }
        }
        else {
            return $this->render('register', [
                        'model' => $model,
            ]);
        }
    }

    /**
     * Sending the account password reset link.
     * @return string|\yii\web\Response
     */
    public function actionReset()
    {
        $model = new ReForm();

        if ($model->load(Yii::$app->request->post())) {

            if ($model->reset()) {
                try {
                    $mailer = Yii::$app->mailer->compose('/mail/reset', [
                                'forum' => $this->module->getParam('name', 'Podium Forum'),
                                'link'  => Url::to(['account/password', 'token' => $model->getUser()->password_reset_token], true)
                            ])->setFrom($this->module->getParam('email', 'no-reply@podium-default.net'))
                            ->setTo($model->getUser()->email)
                            ->setSubject(Yii::t('podium/mail', '{NAME} password reset link', ['NAME' => $this->module->getParam('name', 'Podium Forum')]));
                    if ($mailer->send()) {
                        Log::info('Password reset link sent', !empty($model->id) ? $model->id : '', __METHOD__);
                        $this->success('The password reset link has been sent to your e-mail address.');
                    }
                    else {
                        Log::error('Mailer error while sending password reset link', !empty($model->id) ? $model->id : '', __METHOD__);
                        $this->error('Sorry! There was some error while sending you the password reset link. Contact administrator about this problem.');
                    }

                    return $this->module->goPodium();
                }
                catch (Exception $e) {
                    Log::error('Error while sending password reset link', !empty($model->id) ? $model->id : '', __METHOD__);
                    $this->error('Sorry! There was some error while sending you the password reset link. Contact administrator about this problem.');
                }
            }
            else {
                $this->error('Sorry! We can not find the account with that user name or e-mail address.');
            }
        }

        return $this->render('reset', [
                    'model' => $model,
        ]);
    }
}