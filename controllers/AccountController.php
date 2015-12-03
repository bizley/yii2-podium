<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\controllers;

use bizley\podium\components\Cache;
use bizley\podium\components\Config;
use bizley\podium\log\Log;
use bizley\podium\models\Content;
use bizley\podium\models\Email;
use bizley\podium\models\LoginForm;
use bizley\podium\models\ReForm;
use bizley\podium\models\User;
use bizley\podium\Module as PodiumModule;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Podium Account controller
 * All actions concerning user account.
 * 
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */
class AccountController extends BaseController
{

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
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'access' => [
                    'class' => AccessControl::className(),
                    'denyCallback' => function ($rule, $action) {
                        return $this->module->goPodium();
                    },
                    'rules' => [
                        [
                            'allow'         => false,
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
            ]
        );
    }
    
    /**
     * Activating the account based on the provided activation token.
     * @param string $token
     * @return \yii\web\Response
     */
    public function actionActivate($token)
    {
        if (PodiumModule::getInstance()->userComponent == PodiumModule::USER_INHERIT) {
            $this->info(Yii::t('podium/flash', 'Please contact the administrator to activate your account.'));
            return $this->module->goPodium();
        }
        
        $model = User::findByActivationToken($token);

        if ($model) {
            $model->setScenario('token');
            if ($model->activate()) {
                Cache::clearAfterActivate();
                Log::info('Account activated', $model->id, __METHOD__);
                $this->success(Yii::t('podium/flash', 'Your account has been activated. You can sign in now.'));
            }
            else {
                Log::error('Error while activating account', $model->id, __METHOD__);
                $this->error(Yii::t('podium/flash', 'Sorry! There was some error while activating your account. Contact administrator about this problem.'));
            }
            return $this->module->goPodium();
        }
        else {
            $this->error(Yii::t('podium/flash', 'The provided activation token is invalid or expired.'));
            return $this->module->goPodium();
        }
    }

    /**
     * Signing in.
     * @return string|\yii\web\Response
     */
    public function actionLogin()
    {
        if (PodiumModule::getInstance()->userComponent == PodiumModule::USER_INHERIT) {
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
     * @return \yii\web\Response
     */
    public function actionNewEmail($token)
    {
        $model = User::findByEmailToken($token);

        if ($model) {
            $model->setScenario('token');
            if ($model->changeEmail()) {
                Log::info('Email address changed', $model->id, __METHOD__);
                $this->success(Yii::t('podium/flash', 'Your new e-mail address has been activated.'));
            }
            else {
                Log::error('Error while activating email', $model->id, __METHOD__);
                $this->error(Yii::t('podium/flash', 'Sorry! There was some error while activating your new e-mail address. Contact administrator about this problem.'));
            }
            return $this->module->goPodium();
        }
        else {
            $this->error(Yii::t('podium/flash', 'The provided activation token is invalid or expired.'));
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
        if (PodiumModule::getInstance()->userComponent == PodiumModule::USER_INHERIT) {
            $this->info(Yii::t('podium/flash', 'Please contact the administrator to change your account password.'));
            return $this->module->goPodium();
        }
        
        $model = User::findByPasswordResetToken($token);

        if ($model) {
            $model->setScenario('passwordChange');
            if ($model->load(Yii::$app->request->post()) && $model->changePassword()) {
                Log::info('Password changed', $model->id, __METHOD__);
                $this->success(Yii::t('podium/flash', 'Your account password has been changed.'));
                return $this->module->goPodium();
            }
            else {
                return $this->render('password', ['model' => $model]);
            }
        }
        else {
            $this->error(Yii::t('podium/flash', 'The provided password reset token is invalid or expired.'));
            return $this->module->goPodium();
        }
    }
    
    /**
     * Resending the account activation link.
     * @return string|\yii\web\Response
     */
    public function actionReactivate()
    {
        if (PodiumModule::getInstance()->userComponent == PodiumModule::USER_INHERIT) {
            $this->info(Yii::t('podium/flash', 'Please contact the administrator to reactivate your account.'));
            return $this->module->goPodium();
        }
        
        $model = new ReForm();

        if ($model->load(Yii::$app->request->post())) {

            if ($model->reactivate()) {
                
                $email = Content::find()->where(['name' => 'email-react'])->limit(1)->one();
                if ($email) {
                    $topic   = $email->topic;
                    $content = $email->content;
                }
                else {
                    $topic   = '{forum} account reactivation';
                    $content = '<p>{forum} Account Activation</p><p>You are receiving this e-mail because someone has started the process of activating the account at {forum}.<br>If this person is you open the following link in your Internet browser and follow the instructions on screen.</p><p>{link}</p><p>If it was not you just ignore this e-mail.</p><p>Thank you!<br>{forum}</p>';
                }
                
                $forum = Config::getInstance()->get('name');
                if (!empty($model->user->email)) {
                    if (Email::queue($model->user->email, 
                            str_replace('{forum}', $forum, $topic),
                            str_replace('{forum}', $forum, str_replace('{link}', Html::a(
                                    Url::to(['account/activate', 'token' => $model->user->activation_token], true),
                                    Url::to(['account/activate', 'token' => $model->user->activation_token], true)
                                ), $content)),
                            !empty($model->user->id) ? $model->user->id : null
                        )) {
                        Log::info('Reactivation link queued', $model->user->id, __METHOD__);
                        $this->success(Yii::t('podium/flash', 'The account activation link has been sent to your e-mail address.'));
                    }
                    else {
                        Log::error('Error while queuing reactivation link', $model->user->id, __METHOD__);
                        $this->error(Yii::t('podium/flash', 'Sorry! There was some error while sending you the account activation link. Contact administrator about this problem.'));
                    }
                }
                else {
                    Log::error('Error while queuing reactivation link - no email set', $model->user->id, __METHOD__);
                    $this->error(Yii::t('podium/flash', 'Sorry! There is no e-mail address saved with your account. Contact administrator about reactivating.'));
                }

                return $this->module->goPodium();
            }
            else {
                $this->error(Yii::t('podium/flash', 'Sorry! We can not find the account with that user name or e-mail address.'));
            }
        }

        return $this->render('reactivate', ['model' => $model]);
    }
    
    /**
     * Registering the new account and sending the activation link.
     * @return string|\yii\web\Response
     */
    public function actionRegister()
    {
        if (PodiumModule::getInstance()->userComponent == PodiumModule::USER_INHERIT) {
            $this->info(Yii::t('podium/flash', 'Please use application Register form to sign up.'));
            return $this->module->goPodium();
        }
        
        $model = new User();
        $model->setScenario('register');
        
        if ($model->load(Yii::$app->request->post()) && $model->register()) {
            
            $email = Content::find()->where(['name' => 'email-reg'])->limit(1)->one();
            if ($email) {
                $topic   = $email->topic;
                $content = $email->content;
            }
            else {
                $topic   = 'Welcome to {forum}! This is your activation link';
                $content = '<p>Thank you for registering at {forum}!</p><p>To activate you account open the following link in your Internet browser:<br>{link}<br></p><p>See you soon!<br>{forum}</p>';
            }

            $forum = Config::getInstance()->get('name');
            if (!empty($model->email)) {
                if (Email::queue($model->email, 
                        str_replace('{forum}', $forum, $topic),
                        str_replace('{forum}', $forum, str_replace('{link}', Html::a(
                                Url::to(['account/activate', 'token' => $model->activation_token], true),
                                Url::to(['account/activate', 'token' => $model->activation_token], true)
                            ), $content)),
                        !empty($model->id) ? $model->id : null
                    )) {
                    Log::info('Activation link queued', !empty($model->id) ? $model->id : '', __METHOD__);
                    $this->success(Yii::t('podium/flash', 'Your account has been created but it is not active yet. Click the activation link that has been sent to your e-mail address.'));
                }
                else {
                    Log::warning('Error while queuing activation link', !empty($model->id) ? $model->id : '', __METHOD__);
                    $this->warning(Yii::t('podium/flash', 'Your account has been created but it is not active yet. '
                            . 'Unfortunately there was some error while sending you the activation link. '
                            . 'Contact administrator about this or try to {resend the link}.', [
                                'resend the link' => Html::a(Yii::t('podium/flash', 'resend the link'), ['account/reactivate'])
                            ]));
                }
            }
            else {
                Log::error('Error while queuing activation link - no email set', $model->id, __METHOD__);
                $this->error(Yii::t('podium/flash', 'Sorry! There is no e-mail address saved with your account. Contact administrator about activating.'));
            }
            
            return $this->module->goPodium();
        }
        
        return $this->render('register', ['model' => $model, 'terms' => Content::findOne(['name' => 'terms'])]);
    }

    /**
     * Sending the account password reset link.
     * @return string|\yii\web\Response
     */
    public function actionReset()
    {
        if (PodiumModule::getInstance()->userComponent == PodiumModule::USER_INHERIT) {
            $this->info(Yii::t('podium/flash', 'Please contact the administrator to reset your account password.'));
            return $this->module->goPodium();
        }
        
        $model = new ReForm();

        if ($model->load(Yii::$app->request->post())) {

            if ($model->reset()) {

                $email = Content::find()->where(['name' => 'email-pass'])->limit(1)->one();
                if ($email) {
                    $topic   = $email->topic;
                    $content = $email->content;
                }
                else {
                    $topic   = '{forum} password reset link';
                    $content = '<p>{forum} Password Reset</p><p>You are receiving this e-mail because someone has started the process of changing the account password at {forum}.<br>If this person is you open the following link in your Internet browser and follow the instructions on screen.</p><p>{link}</p><p>If it was not you just ignore this e-mail.</p><p>Thank you!<br>{forum}</p>';
                }

                $forum = Config::getInstance()->get('name');
                if (!empty($model->email)) {
                    if (Email::queue($model->user->email, 
                            str_replace('{forum}', $forum, $topic),
                            str_replace('{forum}', $forum, str_replace('{link}', Html::a(
                                    Url::to(['account/password', 'token' => $model->user->password_reset_token], true),
                                    Url::to(['account/password', 'token' => $model->user->password_reset_token], true)
                                ), $content)),
                            !empty($model->user->id) ? $model->user->id : null
                        )) {
                        Log::info('Password reset link queued', $model->user->id, __METHOD__);
                        $this->success(Yii::t('podium/flash', 'The password reset link has been sent to your e-mail address.'));
                    }
                    else {
                        Log::error('Error while queuing password reset link', $model->user->id, __METHOD__);
                        $this->error(Yii::t('podium/flash', 'Sorry! There was some error while sending you the password reset link. Contact administrator about this problem.'));
                    }
                }
                else {
                    Log::error('Error while queuing password reset link - no email set', $model->user->id, __METHOD__);
                    $this->error(Yii::t('podium/flash', 'Sorry! There is no e-mail address saved with your account. Contact administrator about resetting password.'));
                }
                
                return $this->module->goPodium();
            }
            else {
                $this->error(Yii::t('podium/flash', 'Sorry! We can not find the account with that user name or e-mail address.'));
            }
        }

        return $this->render('reset', ['model' => $model]);
    }
}
