<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
use bizley\podium\components\Helper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use Zelenin\yii\widgets\Summernote\Summernote;

$this->title = Yii::t('podium/view', 'Contents');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Administration Dashboard'), 'url' => ['admin/index']];
$this->params['breadcrumbs'][] = $this->title;

echo $this->render('/elements/admin/_navbar', ['active' => 'contents']);

?>

<br>

<div class="row">
    <div class="col-sm-3">
        <?= Html::beginTag('ul', ['class' => 'nav nav-pills nav-stacked']); ?>
        <?= Html::tag('li', Html::a(Html::tag('span', '', ['class' => 'glyphicon glyphicon-chevron-right']) . ' ' . Yii::t('podium/view', 'Forum Terms and Conditions'), ['admin/contents/terms']), ['role' => 'presentation', 'class' => $model->name == 'terms' ? 'active' : null]); ?>
        <?= Html::tag('li', Html::a(Html::tag('span', '', ['class' => 'glyphicon glyphicon-chevron-right']) . ' ' . Yii::t('podium/view', 'Registration e-mail'), ['admin/contents/email-reg']), ['role' => 'presentation', 'class' => $model->name == 'email-reg' ? 'active' : null]); ?>
        <?= Html::tag('li', Html::a(Html::tag('span', '', ['class' => 'glyphicon glyphicon-chevron-right']) . ' ' . Yii::t('podium/view', 'New address activation e-mail'), ['admin/contents/email-new']), ['role' => 'presentation', 'class' => $model->name == 'email-new' ? 'active' : null]); ?>
        <?= Html::tag('li', Html::a(Html::tag('span', '', ['class' => 'glyphicon glyphicon-chevron-right']) . ' ' . Yii::t('podium/view', 'Account reactivation e-mail'), ['admin/contents/email-react']), ['role' => 'presentation', 'class' => $model->name == 'email-react' ? 'active' : null]); ?>
        <?= Html::tag('li', Html::a(Html::tag('span', '', ['class' => 'glyphicon glyphicon-chevron-right']) . ' ' . Yii::t('podium/view', 'Password reset e-mail'), ['admin/contents/email-pass']), ['role' => 'presentation', 'class' => $model->name == 'email-pass' ? 'active' : null]); ?>
        <?= Html::tag('li', Html::a(Html::tag('span', '', ['class' => 'glyphicon glyphicon-chevron-right']) . ' ' . Yii::t('podium/view', 'New post in subscribed thread'), ['admin/contents/email-sub']), ['role' => 'presentation', 'class' => $model->name == 'email-sub' ? 'active' : null]); ?>
        <?= Html::endTag('ul'); ?>
<?php if (substr($model->name, 0, 6) == 'email-'): ?>
        <br>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><?= Yii::t('podium/view', 'Content variables') ?></h3>
            </div>
            <div class="panel-body">
                <strong>{forum}</strong> <?= Yii::t('podium/view', 'This forum\'s name') ?><br>
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
                    <?= $form->field($model, 'content')->label(false)->widget(Summernote::className(), [
                            'clientOptions' => [
                                'height'     => '300',
                                'lang'       => Yii::$app->language != 'en-US' ? Yii::$app->language : null,
                                'codemirror' => null,
                                'toolbar'    => Helper::summerNoteToolbars(),
                            ],
                        ]) ?>
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