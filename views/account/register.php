<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */

use bizley\podium\components\Config;
use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;
use yii\helpers\Html;
use Zelenin\yii\widgets\Recaptcha\widgets\Recaptcha;

$this->title = Yii::t('podium/view', 'Registration');
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs("$('[data-toggle=\"popover\"]').popover()");

?>
<div class="row">
    <div class="col-sm-4">
        <?php $form = ActiveForm::begin(['id' => 'register-form']); ?>
            <div class="form-group">
                <?= $form->field($model, 'email')->textInput(['placeholder' => Yii::t('podium/view', 'E-mail')])->label(false) ?>
            </div>
            <div class="form-group">
                <?= $form->field($model, 'password')->passwordInput([
                    'placeholder'    => Yii::t('podium/view', 'Password'),
                    'data-container' => 'body',
                    'data-toggle'    => 'popover',
                    'data-placement' => 'right',
                    'data-content'   => Yii::t('podium/view', 'Password must contain uppercase and lowercase letter, digit, and be at least 6 characters long.'),
                    'data-trigger'   => 'focus'
                ])->label(false) ?>
            </div>
            <div class="form-group">
                <?= $form->field($model, 'password_repeat')->passwordInput(['placeholder' => Yii::t('podium/view', 'Repeat Password')])->label(false) ?>
            </div>
            <div class="form-group">
                <?= $form->field($model, 'tos')->checkBox()->label('<small>' . Yii::t('podium/view', 'I have read and agree to the Terms and Conditions') . ' <span class="glyphicon glyphicon-circle-arrow-right"></span></small>') ?>
            </div>
<?php if (Config::getInstance()->get('use_captcha')): ?>
<?php if (Config::getInstance()->get('recaptcha_sitekey') !== '' && Config::getInstance()->get('recaptcha_secretkey') !== ''): ?>
            <div class="form-group">
                <?= $form->field($model, 'captcha')->widget(Recaptcha::className(), [
                    'clientOptions' => [
                        'data-sitekey' => Config::getInstance()->get('recaptcha_sitekey')
                    ]
                ]) ?>
            </div>
<?php else: ?>
            <div class="form-group">
                <?= $form->field($model, 'captcha')->widget(Captcha::classname(), [
                    'captchaAction' => ['account/captcha'],
                    'options'       => [
                        'class'          => 'form-control',
                        'placeholder'    => Yii::t('podium/view', 'Type the CAPTCHA text'),
                        'data-container' => 'body',
                        'data-toggle'    => 'popover',
                        'data-placement' => 'right',
                        'data-content'   => Yii::t('podium/view', 'Type the CAPTCHA text displayed above. Click the image to generate another CAPTCHA code.'),
                        'data-trigger'   => 'focus',
                    ],
                ]) ?>
            </div>
<?php endif; ?>
<?php endif; ?>
            <?= Html::submitButton('<span class="glyphicon glyphicon-ok-sign"></span> ' . Yii::t('podium/view', 'Register new account'), ['class' => 'btn btn-block btn-primary', 'name' => 'register-button']) ?>
        <?php ActiveForm::end(); ?>
        <br>
    </div>
    <div class="col-sm-8">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <?= Yii::t('podium/view', 'Forum Terms and Conditions') ?>
                </h4>
            </div>
            <div class="panel-body">
                <?= $terms ? $terms->content : Yii::t('podium/view', 'TO BE ANNOUNCED') ?>
            </div>
        </div>
    </div>
</div><br>
