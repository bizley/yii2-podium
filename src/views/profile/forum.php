<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

use bizley\podium\components\Helper;
use bizley\podium\models\Meta;
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
                            <?= $form
                                ->field($model, 'signature')
                                ->label(Yii::t('podium/view', 'Signature under each post'))
                                ->widget(Quill::className(), ['toolbar' => 'basic', 'options' => ['style' => 'height:100px']]) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <?= $form
                                ->field($model, 'gravatar')
                                ->checkbox(['disabled' => empty($user->email)])
                                ->label('<strong>' . Yii::t('podium/view', 'Use Gravatar image as avatar') . '</strong>')
                                ->hint(
                                    (empty($user->email)
                                        ? Html::tag('span', Yii::t('podium/view', 'You need email address set to use Gravatar.'), ['class' => 'text-danger pull-right'])
                                        : ''
                                    ) . Html::a(Yii::t('podium/view', 'What is Gravatar?'), 'http://gravatar.com', ['target' => 'gravatar'])
                                ) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <?= $form->field($model, 'image')->label(Yii::t('podium/view', 'Or upload your own avatar'))->widget(FileInput::className(), [
                                'options'       => ['accept' => 'image/*'],
                                'pluginOptions' => ['allowedFileExtensions' => ['jpg', 'jpeg', 'gif', 'png']]
                            ])->hint(Yii::t('podium/view', 'Square avatars look best.') . '<br>' . Yii::t('podium/view', 'Maximum size is {size}, {width}x{height} pixels; png, jpg and gif images only.', ['size' => ceil(Meta::MAX_SIZE / 1024) . 'kB', 'width' => Meta::MAX_WIDTH, 'height' => Meta::MAX_HEIGHT])) ?>
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
