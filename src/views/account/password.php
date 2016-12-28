<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = Yii::t('podium/view', 'Change password');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="row">
    <div class="col-sm-12">
        <div class="alert alert-info">
            <span class="glyphicon glyphicon-info-sign"></span> <?= Yii::t('podium/view', 'Enter new password for your account. Password must contain uppercase and lowercase letter, digit, and be at least {chars} characters long.', ['chars' => 6]) ?>
        </div>
    </div>
    <div class="col-sm-4 col-sm-offset-4">
        <?php $form = ActiveForm::begin(['id' => 'password-form']); ?>
            <div class="form-group">
                <?= $form->field($model, 'password')->passwordInput(['placeholder' => Yii::t('podium/view', 'New password'), 'autofocus' => true])->label(false) ?>
            </div>
            <div class="form-group">
                <?= $form->field($model, 'passwordRepeat')->passwordInput(['placeholder' => Yii::t('podium/view', 'Repeat new password')])->label(false) ?>
            </div>
            <div class="form-group">
                <?= Html::submitButton('<span class="glyphicon glyphicon-ok-sign"></span> ' . Yii::t('podium/view', 'Change password'), ['class' => 'btn btn-block btn-danger', 'name' => 'password-button']) ?>
            </div>
        <?php ActiveForm::end(); ?>
    </div>
</div><br>
