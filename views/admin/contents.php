<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */

use bizley\quill\Quill;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = Yii::t('podium/view', 'Contents');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Administration Dashboard'), 'url' => ['admin/index']];
$this->params['breadcrumbs'][] = $this->title;

echo $this->render('/elements/admin/_navbar', ['active' => 'contents']);

?>
<br>
<div class="row">
    <div class="col-sm-3">
        <ul class="nav nav-pills nav-stacked">
            <li role="presentation" class="<?= $model->name == 'terms' ? 'active' : '' ?>"><a href="<?= Url::to(['admin/contents', 'name' => 'terms']) ?>"><span class="glyphicon glyphicon-chevron-right"></span> <?= Yii::t('podium/view', 'Forum Terms and Conditions') ?></a></li>
            <li role="presentation" class="<?= $model->name == 'email-reg' ? 'active' : '' ?>"><a href="<?= Url::to(['admin/contents', 'name' => 'email-reg']) ?>"><span class="glyphicon glyphicon-chevron-right"></span> <?= Yii::t('podium/view', 'Registration e-mail') ?></a></li>
            <li role="presentation" class="<?= $model->name == 'email-new' ? 'active' : '' ?>"><a href="<?= Url::to(['admin/contents', 'name' => 'email-new']) ?>"><span class="glyphicon glyphicon-chevron-right"></span> <?= Yii::t('podium/view', 'New address activation e-mail') ?></a></li>
            <li role="presentation" class="<?= $model->name == 'email-react' ? 'active' : '' ?>"><a href="<?= Url::to(['admin/contents', 'name' => 'email-react']) ?>"><span class="glyphicon glyphicon-chevron-right"></span> <?= Yii::t('podium/view', 'Account reactivation e-mail') ?></a></li>
            <li role="presentation" class="<?= $model->name == 'email-pass' ? 'active' : '' ?>"><a href="<?= Url::to(['admin/contents', 'name' => 'email-pass']) ?>"><span class="glyphicon glyphicon-chevron-right"></span> <?= Yii::t('podium/view', 'Password reset e-mail') ?></a></li>
            <li role="presentation" class="<?= $model->name == 'email-sub' ? 'active' : '' ?>"><a href="<?= Url::to(['admin/contents', 'name' => 'email-sub']) ?>"><span class="glyphicon glyphicon-chevron-right"></span> <?= Yii::t('podium/view', 'New post in subscribed thread') ?></a></li>
        </ul>
<?php if (substr($model->name, 0, 6) == 'email-'): ?>
        <br>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><?= Yii::t('podium/view', 'Content variables') ?></h3>
            </div>
            <div class="panel-body">
                <strong>{forum}</strong> <?= Yii::t('podium/view', "This forum's name") ?><br>
                <strong>{link}</strong> <?= Yii::t('podium/view', 'The link coming with email') ?><br>
            </div>
        </div>
<?php endif; ?>
    </div>
    <div class="col-sm-9">
        <?php $form = ActiveForm::begin(['id' => 'content-form']); ?>
            <div class="row">
                <div class="col-sm-12">
                    <?= $form->field($model, 'topic')->textInput(['placeholder' => Yii::t('podium/view', 'Topic')])->label(false) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <?= $form->field($model, 'content')->label(false)->widget(Quill::className(), ['options' => ['style' => 'height:500px']]) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <?= Html::submitButton('<span class="glyphicon glyphicon-ok-sign"></span> ' . Yii::t('podium/view', 'Save Content'), ['class' => 'btn btn-block btn-primary', 'name' => 'save-button']) ?>
                </div>
            </div>
        <?php ActiveForm::end(); ?>
    </div>
</div><br>
