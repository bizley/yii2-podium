<?php

namespace bizley\podium\controllers;

use bizley\podium\filters\AccessControl;
use bizley\podium\log\Log;
use bizley\podium\models\Content;
use bizley\podium\models\forms\LoginForm;
use bizley\podium\models\forms\ReactivateForm;
use bizley\podium\models\forms\ResetForm;
use bizley\podium\models\User;
use bizley\podium\PodiumCache;
use Yii;
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
                'denyCallback' => function ($rule, $action) {
                    return $this->module->goPodium();
                },
                'rules' => [
                    ['class' => 'bizley\podium\filters\InstallRule'],
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
     * Redirect in case of guest access type.
     * @param Action $action the action to be executed
     * @return bool
     * @since 0.6
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        if ($this->accessType === 0) {
            return $this->module->goPodium();
        }
        return true;
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
            PodiumCache::clearAfter('activate');
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

        $model = new LoginForm();
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
     * Registering the new account and sending the activation link.
     * @return string|Response
     */
    public function actionRegister()
    {
        if ($this->module->userComponent !== true) {
            $this->info(Yii::t('podium/flash', "Please use application's Register form to sign up."));
            return $this->module->goPodium();
        }

        if ($this->module->podiumConfig->get('registration_off') == '1') {
            $this->info(Yii::t('podium/flash', 'User registration is currently not allowed.'));
            return $this->module->goPodium();
        }

        $model = new User();
        $model->scenario = 'register';
        if ($model->load(Yii::$app->request->post())) {
            $result = $model->register();
            if ($result == User::RESP_OK) {
                Log::info('Activation link queued', !empty($model->id) ? $model->id : '', __METHOD__);
                $this->success(Yii::t('podium/flash', 'Your account has been created but it is not active yet. Click the activation link that will be sent to your e-mail address in few minutes.'));
                return $this->module->goPodium();
            }
            if ($result == User::RESP_EMAIL_SEND_ERR) {
                Log::warning('Error while queuing activation link', !empty($model->id) ? $model->id : '', __METHOD__);
                $this->warning(Yii::t('podium/flash', 'Your account has been created but it is not active yet. Unfortunately there was some error while sending you the activation link. Contact administrator about this or try to {resend the link}.', [
                    'resend the link' => Html::a(Yii::t('podium/flash', 'resend the link'), ['account/reactivate'])
                ]));
                return $this->module->goPodium();
            }
            if ($result == User::RESP_NO_EMAIL_ERR) {
                Log::error('Error while queuing activation link - no email set', !empty($model->id) ? $model->id : '', __METHOD__);
                $this->error(Yii::t('podium/flash', 'Sorry! There is no e-mail address saved with your account. Contact administrator about activating.'));
                return $this->module->goPodium();
            }
        }
        $model->captcha = null;

        return $this->render('register', [
            'model' => $model,
            'terms' => Content::fill(Content::TERMS_AND_CONDS)]
        );
    }

    /**
     * Sending the account password reset link.
     * @return string|Response
     */
    public function actionReset()
    {
        return $this->reformRun(
            Yii::t('podium/flash', 'Please contact the administrator to reset your account password.'),
            new ResetForm(),
            [
                'error' => 'Error while queuing password reset link',
                'info' => 'Password reset link queued',
                'method' => __METHOD__
            ],
            'reset'
        );
    }

    /**
     * Resending the account activation link.
     * @return string|Response
     */
    public function actionReactivate()
    {
        return $this->reformRun(
            Yii::t('podium/flash', 'Please contact the administrator to reactivate your account.'),
            new ReactivateForm(),
            [
                'error' => 'Error while queuing reactivation link',
                'info' => 'Reactivation link queued',
                'method' => __METHOD__
            ],
            'reactivate'
        );
    }

    /**
     * Runs actions processed with email.
     * @param string $componentInfo
     * @param ReactivateForm|ResetForm $model
     * @param array $log
     * @return string|Response
     * @since 0.6
     */
    protected function reformRun($componentInfo, $model, $log, $view)
    {
        if ($this->module->userComponent !== true) {
            $this->info($componentInfo);
            return $this->module->goPodium();
        }

        if ($model->load(Yii::$app->request->post())) {
            list($error, $message, $back) = $model->run();
            if ($error) {
                Log::error($log['error'], !empty($model->user->id) ? $model->user->id : null, $log['method']);
                if (!empty($message)) {
                    $this->error($message);
                }
            } else {
                Log::info($log['info'], $model->user->id, $log['method']);
                if (!empty($message)) {
                    $this->success($message);
                }
            }
            if ($back) {
                return $this->module->goPodium();
            }
        }
        return $this->render($view, ['model' => $model]);
    }
}
