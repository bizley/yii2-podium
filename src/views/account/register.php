<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\web\View;

$this->title                   = Yii::t('podium/view', 'Registration');
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs('jQuery(\'[data-toggle="popover"]\').popover()', View::POS_READY, 'bootstrap-popover');
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
                <textarea class="form-control" rows="6"><?= Yii::t('podium/view', 'TBD') ?></textarea>
            </div>
        </div>
    </div>
</div><br>