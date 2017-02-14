<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

use bizley\podium\Podium;
use himiklab\yii2\recaptcha\ReCaptcha;
use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;
use yii\helpers\Html;

$this->title = Yii::t('podium/view', 'Registration');
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs("$('[data-toggle=\"popover\"]').popover();");

?>
<div class="row">
    <div class="col-sm-4">
        <?php $form = ActiveForm::begin(['id' => 'register-form']); ?>
            <?= $form->field($model, 'username')->textInput([
                'placeholder' => Yii::t('podium/view', 'Username'),
                'autofocus' => true,
                'data-container' => 'body',
                'data-toggle'    => 'popover',
                'data-placement' => 'right',
                'data-content'   => Yii::t('podium/view', 'Username must start with a letter, contain only letters, digits and underscores, and be at least 3 characters long.'),
                'data-trigger'   => 'focus'
            ])->label(false) ?>
            <?= $form->field($model, 'email')->textInput(['placeholder' => Yii::t('podium/view', 'E-mail')])->label(false) ?>
            <?= $form->field($model, 'password')->passwordInput([
                'placeholder'    => Yii::t('podium/view', 'Password'),
                'data-container' => 'body',
                'data-toggle'    => 'popover',
                'data-placement' => 'right',
                'data-content'   => Yii::t('podium/view', 'Password must contain uppercase and lowercase letter, digit, and be at least 6 characters long.'),
                'data-trigger'   => 'focus'
            ])->label(false) ?>
            <?= $form->field($model, 'passwordRepeat')->passwordInput(['placeholder' => Yii::t('podium/view', 'Repeat password')])->label(false) ?>
            <?= $form->field($model, 'tos')->checkBox()->label('<small>' . Yii::t('podium/view', 'I have read and agree to the Terms and Conditions') . ' <span class="glyphicon glyphicon-circle-arrow-right"></span></small>') ?>
<?php if (Podium::getInstance()->podiumConfig->get('use_captcha')): ?>
<?php if (Podium::getInstance()->podiumConfig->get('recaptcha_sitekey') !== '' && Podium::getInstance()->podiumConfig->get('recaptcha_secretkey') !== ''): ?>
            <?= $form->field($model, 'captcha')->widget(ReCaptcha::className(), [
                'siteKey' => Podium::getInstance()->podiumConfig->get('recaptcha_sitekey'),
            ]) ?>
<?php else: ?>
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
