<?php

namespace bizley\podium\controllers;

use bizley\podium\components\Cache;
use bizley\podium\log\Log;
use bizley\podium\models\Content;
use bizley\podium\models\LoginForm;
use bizley\podium\models\ReForm;
use bizley\podium\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Html;
use yii\web\Response;

/**
 * Podium Account controller
 * All actions concerning user account.
 * 
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */
class AccountController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'user' => $this->module->user,
                'denyCallback' => function ($rule, $action) {
                    return $this->module->goPodium();
                },
                'rules' => [
                    [
                        'allow' => false,
                        'matchCallback' => function ($rule, $action) {
                            return !$this->module->getInstalled();
                        },
                        'denyCallback' => function ($rule, $action) {
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
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'testLimit' => 1
            ],
        ];
    }
    
    /**
     * Activating the account based on the provided activation token.
     * @param string $token
     * @return Response
     */
    public function actionActivate($token)
    {
        if ($this->module->userComponent !== true) {
            $this->info(Yii::t('podium/flash', 'Please contact the administrator to activate your account.'));
            return $this->module->goPodium();
        }
        
        $model = User::findByActivationToken($token);
        if (!$model) {
            $this->error(Yii::t('podium/flash', 'The provided activation token is invalid or expired.'));
            return $this->module->goPodium();
        }
        $model->scenario = 'token';
        if ($model->activate()) {
            Cache::clearAfter('activate');
            Log::info('Account activated', $model->id, __METHOD__);
            $this->success(Yii::t('podium/flash', 'Your account has been activated. You can sign in now.'));
        } else {
            Log::error('Error while activating account', $model->id, __METHOD__);
            $this->error(Yii::t('podium/flash', 'Sorry! There was some error while activating your account. Contact administrator about this problem.'));
        }
        return $this->module->goPodium();
    }

    /**
     * Signing in.
     * @return string|Response
     */
    public function actionLogin()
    {
        if ($this->module->userComponent !== true) {
            $this->info(Yii::t('podium/flash', 'Please use application Login form to sign in.'));
            return $this->module->goPodium();
        }
        
        $model = new LoginForm;
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->module->goPodium();
        }

        return $this->render('login', ['model' => $model]);
    }
    
    /**
     * Activating new email address based on the provided token.
     * @param string $token
     * @return Response
     */
    public function actionNewEmail($token)
    {
        $model = User::findByEmailToken($token);
        if (!$model) {
            $this->error(Yii::t('podium/flash', 'The provided activation token is invalid or expired.'));
            return $this->module->goPodium();
        }
        
        $model->scenario = 'token';
        if ($model->changeEmail()) {
            Log::info('Email address changed', $model->id, __METHOD__);
            Yii::$app->session->removeFlash('warning'); // removes warning about not having the email
            $this->success(Yii::t('podium/flash', 'Your new e-mail address has been activated.'));
        } else {
            Log::error('Error while activating email', $model->id, __METHOD__);
            $this->error(Yii::t('podium/flash', 'Sorry! There was some error while activating your new e-mail address. Contact administrator about this problem.'));
        }
        return $this->module->goPodium();
    }

    /**
     * Changing the account password with provided token.
     * @param string $token
     * @return string|Response
     */
    public function actionPassword($token)
    {
        if ($this->module->userComponent !== true) {
            $this->info(Yii::t('podium/flash', 'Please contact the administrator to change your account password.'));
            return $this->module->goPodium();
        }
        
        $model = User::findByPasswordResetToken($token);
        if (!$model) {
            $this->error(Yii::t('podium/flash', 'The provided password reset token is invalid or expired.'));
            return $this->module->goPodium();
        }
        $model->scenario = 'passwordChange';
        if ($model->load(Yii::$app->request->post()) && $model->changePassword()) {
            Log::info('Password changed', $model->id, __METHOD__);
            $this->success(Yii::t('podium/flash', 'Your account password has been changed.'));
            return $this->module->goPodium();
        }
        return $this->render('password', ['model' => $model]);
    }
    
    /**
     * Resending the account activation link.
     * @return string|Response
     */
    public function actionReactivate()
    {
        if ($this->module->userComponent !== true) {
            $this->info(Yii::t('podium/flash', 'Please contact the administrator to reactivate your account.'));
            return $this->module->goPodium();
        }
        
        $model = new ReForm;
        if ($model->load(Yii::$app->request->post())) {
            switch ($model->reactivate()) {
                case ReForm::RESP_OK:
                    Log::info('Reactivation link queued', $model->user->id, __METHOD__);
                    $this->success(Yii::t('podium/flash', 'The account activation link has been sent to your e-mail address.'));
                    return $this->module->goPodium();
                case ReForm::RESP_EMAIL_SEND_ERR:
                    Log::error('Error while queuing reactivation link', $model->user->id, __METHOD__);
                    $this->error(Yii::t('podium/flash', 'Sorry! There was some error while sending you the account activation link. Contact administrator about this problem.'));
                    return $this->module->goPodium();
                case ReForm::RESP_NO_EMAIL_ERR:
                    Log::error('Error while queuing reactivation link - no email set', $model->user->id, __METHOD__);
                    $this->error(Yii::t('podium/flash', 'Sorry! There is no e-mail address saved with your account. Contact administrator about reactivating.'));
                    return $this->module->goPodium();
                case ReForm::RESP_NO_USER_ERR:
                    $this->error(Yii::t('podium/flash', 'Sorry! We can not find the account with that user name or e-mail address.'));
            }
        }
        return $this->render('reactivate', ['model' => $model]);
    }
    
    /**
     * Registering the new account and sending the activation link.
     * @return string|Response
     */
    public function actionRegister()
    {
        if ($this->module->userComponent !== true) {
            $this->info(Yii::t('podium/flash', "Please use application's Register form to sign up."));
            return $this->module->goPodium();
        }
        
        if ($this->module->config->get('registration_off') == '1') {
            $this->info(Yii::t('podium/flash', 'User registration is currently not allowed.'));
            return $this->module->goPodium();
        }
        
        $model = new User;
        $model->scenario = 'register';
        if ($model->load(Yii::$app->request->post())) {
            switch ($model->register()) {
                case User::RESP_OK:
                    Log::info('Activation link queued', !empty($model->id) ? $model->id : '', __METHOD__);
                    $this->success(
                        Yii::t('podium/flash', 
                            'Your account has been created but it is not active yet. Click the activation link that will be sent to your e-mail address in few minutes.'
                        )
                    );
                    return $this->module->goPodium();
                case User::RESP_EMAIL_SEND_ERR:
                    Log::warning('Error while queuing activation link', !empty($model->id) ? $model->id : '', __METHOD__);
                    $this->warning(
                        Yii::t('podium/flash', 
                            'Your account has been created but it is not active yet. Unfortunately there was some error while sending you the activation link. Contact administrator about this or try to {resend the link}.', [
                                'resend the link' => Html::a(Yii::t('podium/flash', 'resend the link'), ['account/reactivate'])
                            ]
                        )
                    );
                    return $this->module->goPodium();
                case User::RESP_NO_EMAIL_ERR:
                    Log::error('Error while queuing activation link - no email set', $model->id, __METHOD__);
                    $this->error(Yii::t('podium/flash', 'Sorry! There is no e-mail address saved with your account. Contact administrator about activating.'));
                    return $this->module->goPodium();
            }
        }
        $model->captcha = null;
        
        return $this->render('register', ['model' => $model, 'terms' => Content::fill(Content::TERMS_AND_CONDS)]);
    }

    /**
     * Sending the account password reset link.
     * @return string|Response
     */
    public function actionReset()
    {
        if ($this->module->userComponent !== true) {
            $this->info(Yii::t('podium/flash', 'Please contact the administrator to reset your account password.'));
            return $this->module->goPodium();
        }
        
        $model = new ReForm;
        if ($model->load(Yii::$app->request->post())) {
            switch ($model->reset()) {
                case ReForm::RESP_OK:
                    Log::info('Password reset link queued', $model->user->id, __METHOD__);
                    $this->success(Yii::t('podium/flash', 'The password reset link has been sent to your e-mail address.'));
                    return $this->module->goPodium();
                case ReForm::RESP_EMAIL_SEND_ERR:
                    Log::error('Error while queuing password reset link', $model->user->id, __METHOD__);
                    $this->error(Yii::t('podium/flash', 'Sorry! There was some error while sending you the password reset link. Contact administrator about this problem.'));
                    return $this->module->goPodium();
                case ReForm::RESP_NO_EMAIL_ERR:
                    Log::error('Error while queuing password reset link - no email set', $model->user->id, __METHOD__);
                    $this->error(Yii::t('podium/flash', 'Sorry! There is no e-mail address saved with your account. Contact administrator about resetting password.'));
                    return $this->module->goPodium();
                case ReForm::RESP_NO_USER_ERR:
                    $this->error(Yii::t('podium/flash', 'Sorry! We can not find the account with that user name or e-mail address.'));
            }
        }
        return $this->render('reset', ['model' => $model]);
    }
}
