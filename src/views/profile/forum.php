<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\bootstrap\ActiveForm;
use bizley\podium\components\Helper;

$this->title                   = Yii::t('podium/view', 'Forum Details');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'My Profile'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs('$(\'[data-toggle="popover"]\').popover()', View::POS_READY, 'bootstrap-popover');
?>
<div class="row">
    <div class="col-sm-3">
        <?= $this->render('/elements/profile/_navbar', ['active' => 'forum']) ?>
    </div>
    <div class="col-sm-6">
        <div class="panel panel-default">
<?php $form = ActiveForm::begin(['id' => 'account-form']); ?>
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-12">
                        <?= $form->field($model, 'username')->textInput([
                            'data-container' => 'body',
                            'data-toggle' => 'popover',
                            'data-placement' => 'right',
                            'data-content' => Yii::t('podium/view', 'Username must start with a letter, contain only letters, digits and underscores, and be at least 3 characters long.'),
                            'data-trigger' => 'focus'
                        ])->label(Yii::t('podium/view', 'Username')) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <?= $form->field($model, 'new_email')->textInput([
                            'placeholder' => Yii::t('podium/view', 'Leave empty if you don\'t want to change it'),
                            'data-container' => 'body',
                            'data-toggle' => 'popover',
                            'data-placement' => 'right',
                            'data-content' => Yii::t('podium/view', 'New e-mail has to be activated first. Activation link will be sent to the new address.'),
                            'data-trigger' => 'focus'
                        ])->label(Yii::t('podium/view', 'New e-mail')) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <?= $form->field($model, 'password')->passwordInput([
                            'placeholder' => Yii::t('podium/view', 'Leave empty if you don\'t want to change it'),
                            'data-container' => 'body',
                            'data-toggle' => 'popover',
                            'data-placement' => 'right',
                            'data-content' => Yii::t('podium/view', 'Password must contain uppercase and lowercase letter, digit, and be at least 6 characters long.'),
                            'data-trigger' => 'focus'
                        ])->label(Yii::t('podium/view', 'New password')) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <?= $form->field($model, 'password_repeat')->passwordInput(['placeholder' => Yii::t('podium/view', 'Leave empty if you don\'t want to change it')])->label(Yii::t('podium/view', 'Repeat new password')) ?>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-12">
                        <?= $form->field($model, 'current_password')->passwordInput()->label(Yii::t('podium/view', 'Current password')) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <?= Html::submitButton('<span class="glyphicon glyphicon-ok-sign"></span> ' . Yii::t('podium/view', 'Save changes'), ['class' => 'btn btn-block btn-primary', 'name' => 'save-button']) ?>
                    </div>
                </div>
            </div>
<?php ActiveForm::end(); ?>
        </div>
    </div>
</div><br>