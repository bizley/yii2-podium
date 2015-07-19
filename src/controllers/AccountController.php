<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\controllers;

use bizley\podium\behaviors\FlashBehavior;
use bizley\podium\components\Cache;
use bizley\podium\components\Config;
use bizley\podium\components\Log;
use bizley\podium\models\Content;
use bizley\podium\models\Email;
use bizley\podium\models\LoginForm;
use bizley\podium\models\ReForm;
use bizley\podium\models\User;
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
            return $this->render('login', ['model' => $model]);
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
                return $this->render('password', ['model' => $model]);
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
                
                $email = Content::find()->where(['name' => 'email-react'])->one();
                if ($email) {
                    $topic   = $email->topic;
                    $content = $email->content;
                }
                else {
                    $topic   = '{forum} account reactivation';
                    $content = '<p>{forum} Account Activation</p><p>You are receiving this e-mail because someone has started the process of activating the account at {forum}.<br />If this person is you open the following link in your Internet browser and follow the instructions on screen.</p><p>{link}</p><p>If it was not you just ignore this e-mail.</p><p>Thank you!<br />{forum}</p>';
                }
                
                $forum = Config::getInstance()->get('name');
                if (Email::queue($model->getUser()->email, 
                        str_replace('{forum}', $forum, $topic),
                        str_replace('{forum}', $forum, str_replace('{link}', Html::a(
                                Url::to(['account/activate', 'token' => $model->getUser()->activation_token], true),
                                Url::to(['account/activate', 'token' => $model->getUser()->activation_token], true)
                            ), $content)),
                        !empty($model->getUser()->id) ? $model->getUser()->id : null
                    )) {
                    Log::info('Reactivation link queued', !empty($model->getUser()->id) ? $model->getUser()->id : '', __METHOD__);
                    $this->success('The account activation link has been sent to your e-mail address.');
                }
                else {
                    Log::error('Error while queuing reactivation link', !empty($model->getUser()->id) ? $model->getUser()->id : '', __METHOD__);
                    $this->error('Sorry! There was some error while sending you the account activation link. Contact administrator about this problem.');
                }

                return $this->module->goPodium();
            }
            else {
                $this->error('Sorry! We can not find the account with that user name or e-mail address.');
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
        $model = new User();
        $model->setScenario('register');
        
        if ($model->load(Yii::$app->request->post()) && $model->register()) {
            
            $email = Content::find()->where(['name' => 'email-reg'])->one();
            if ($email) {
                $topic   = $email->topic;
                $content = $email->content;
            }
            else {
                $topic   = 'Welcome to {forum}! This is your activation link';
                $content = '<p>Thank you for registering at {forum}!</p><p>To activate you account open the following link in your Internet browser:<br />{link}<br /></p><p>See you soon!<br />{forum}</p>';
            }

            $forum = Config::getInstance()->get('name');
            if (Email::queue($model->email, 
                    str_replace('{forum}', $forum, $topic),
                    str_replace('{forum}', $forum, str_replace('{link}', Html::a(
                            Url::to(['account/activate', 'token' => $model->activation_token], true),
                            Url::to(['account/activate', 'token' => $model->activation_token], true)
                        ), $content)),
                    !empty($model->id) ? $model->id : null
                )) {
                Log::info('Activation link queued', !empty($model->id) ? $model->id : '', __METHOD__);
                $this->success('Your account has been created but it is not active yet. Click the activation link that has been sent to your e-mail address.');
            }
            else {
                Log::warning('Error while queuing activation link', !empty($model->id) ? $model->id : '', __METHOD__);
                $this->warning('Your account has been created but it is not active yet. '
                        . 'Unfortunately there was some error while sending you the activation link. '
                        . 'Contact administrator about this or try to {link}resend the link{closelink}.', [
                    'link'      => Html::beginTag('a', ['href' => Url::to('account/reactivate')]),
                    'closelink' => Html::endTag('a')
                ]);
            }
            
            return $this->module->goPodium();
        }
        
        return $this->render('register', ['model' => $model, 'terms' => Content::find()->where(['name' => 'terms'])->one()]);
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

                $email = Content::find()->where(['name' => 'email-pass'])->one();
                if ($email) {
                    $topic   = $email->topic;
                    $content = $email->content;
                }
                else {
                    $topic   = '{forum} password reset link';
                    $content = '<p>{forum} Password Reset</p><p>You are receiving this e-mail because someone has started the process of changing the account password at {forum}.<br />If this person is you open the following link in your Internet browser and follow the instructions on screen.</p><p>{link}</p><p>If it was not you just ignore this e-mail.</p><p>Thank you!<br />{forum}</p>';
                }

                $forum = Config::getInstance()->get('name');
                if (Email::queue($model->getUser()->email, 
                        str_replace('{forum}', $forum, $topic),
                        str_replace('{forum}', $forum, str_replace('{link}', Html::a(
                                Url::to(['account/password', 'token' => $model->getUser()->password_reset_token], true),
                                Url::to(['account/password', 'token' => $model->getUser()->password_reset_token], true)
                            ), $content)),
                        !empty($model->getUser()->id) ? $model->getUser()->id : null
                    )) {
                    Log::info('Password reset link queued', !empty($model->getUser()->id) ? $model->getUser()->id : '', __METHOD__);
                    $this->success('The password reset link has been sent to your e-mail address.');
                }
                else {
                    Log::error('Error while queuing password reset link', !empty($model->getUser()->id) ? $model->getUser()->id : '', __METHOD__);
                    $this->error('Sorry! There was some error while sending you the password reset link. Contact administrator about this problem.');
                }
                
                return $this->module->goPodium();
            }
            else {
                $this->error('Sorry! We can not find the account with that user name or e-mail address.');
            }
        }

        return $this->render('reset', ['model' => $model]);
    }
}