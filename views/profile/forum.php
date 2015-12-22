<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */

use bizley\podium\components\Helper;
use bizley\quill\Quill;
use cebe\gravatar\Gravatar;
use kartik\file\FileInput;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('podium/view', 'Forum Details');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'My Profile'), 'url' => ['profile/index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs("$('[data-toggle=\"popover\"]').popover();");

?>
<div class="row">
    <div class="col-sm-3">
        <?= $this->render('/elements/profile/_navbar', ['active' => 'forum']) ?>
    </div>
    <div class="col-sm-6">
        <div class="panel panel-default">
            <?php $form = ActiveForm::begin(['id' => 'forum-form', 'options' => ['enctype' => 'multipart/form-data']]); ?>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <?= $form->field($model, 'location')->textInput(['autocomplete' => 'off'])->label(Yii::t('podium/view', 'Whereabouts')) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <?= $form->field($model, 'signature')->label(Yii::t('podium/view', 'Signature under each post'))->widget(Quill::className(), ['toolbar' => 'basic', 'options' => ['style' => 'height:100px']]) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <a href="http://gravatar.com" target="_blank" class="pull-right"><?= Yii::t('podium/view', 'What is Gravatar?') ?></a>
                            <?= $form->field($model, 'gravatar')->checkbox()->label('<strong>' . Yii::t('podium/view', 'Use Gravatar image as avatar') . '</strong>') ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <?= $form->field($model, 'image')->label(Yii::t('podium/view', 'Or upload your own avatar'))->widget(FileInput::className(), [
                                'options'       => ['accept' => 'image/*'],
                                'pluginOptions' => ['allowedFileExtensions' => ['jpg', 'jpeg', 'gif', 'png']]]) ?>
                            <small><?= Yii::t('podium/view', 'Maximum size is {size}, {width} x {height} pixels, png, jpg and gif images only.', ['size' => '500kB', 'width' => 500, 'height' => 500]) ?></small>
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-sm-12">
                            <?= Html::submitButton('<span class="glyphicon glyphicon-ok-sign"></span> ' . Yii::t('podium/view', 'Save changes'), ['class' => 'btn btn-block btn-primary', 'name' => 'save-button']) ?>
                        </div>
                    </div>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <div class="col-sm-3">
<?php if (!empty($model->gravatar)): ?>
        <?= Gravatar::widget([
            'email'        => $user->email,
            'defaultImage' => 'identicon',
            'rating'       => 'r',
            'options'      => [
                'alt'   => Yii::t('podium/view', 'Your Gravatar image'),
                'class' => 'img-circle img-responsive',
            ]]); ?>
<?php elseif (!empty($model->avatar)): ?>
        <img class="img-circle img-responsive" src="/avatars/<?= $model->avatar ?>" alt="<?= Yii::t('podium/view', 'Your avatar') ?>">
<?php else: ?>
        <img class="img-circle img-responsive" src="<?= Helper::defaultAvatar() ?>" alt="<?= Yii::t('podium/view', 'Default avatar') ?>">
<?php endif; ?>
    </div>
</div><br>
