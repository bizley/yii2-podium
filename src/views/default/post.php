<?php

use bizley\podium\components\Helper;
use bizley\podium\widgets\Avatar;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Alert;
use yii\helpers\Html;
use Zelenin\yii\widgets\Summernote\Summernote;

$this->title                   = Yii::t('podium/view', 'New Reply');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Main Forum'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Html::encode($category->name), 'url' => ['category', 'id' => $category->id, 'slug' => $category->slug]];
$this->params['breadcrumbs'][] = ['label' => Html::encode($forum->name), 'url' => ['forum', 'cid' => $forum->category_id, 'id' => $forum->id, 'slug' => $forum->slug]];
$this->params['breadcrumbs'][] = ['label' => Html::encode($thread->name), 'url' => ['thread', 'cid' => $thread->category_id, 'fid' => $thread->forum_id, 'id' => $thread->id, 'slug' => $thread->slug]];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php if (!empty($preview)): ?>
<div class="row">
    <div class="col-sm-10 col-sm-offset-2">
        <?= Alert::widget(['body' => '<strong><small>' . Yii::t('podium/view', 'Post Preview') . '</small></strong>:<hr>' . $preview, 'options' => ['class' => 'alert-info']]); ?>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-sm-2 text-center">
        <?= Avatar::widget(['author' => Yii::$app->user->getIdentity(), 'showName' => false]) ?>
    </div>
    <div class="col-sm-10">
        <div class="popover right podium">
            <div class="arrow"></div>
            <div class="popover-title">
                <small class="pull-right"><?= Html::tag('span', Yii::t('podium/view', 'In a while'), ['data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'As soon as you click Post Reply')]); ?></small>
                <?= Yii::$app->user->getIdentity()->getPodiumTag() ?>
            </div>
            <div class="popover-content podium-content">
<?php $form = ActiveForm::begin(['id' => 'new-post-form']); ?>
                <div class="row">
                    <div class="col-sm-12">
                        <?= $form->field($model, 'content')->label(false)->widget(Summernote::className(), [
                            'clientOptions' => [
                                'height' => '200',
                                'lang' => Yii::$app->language != 'en-US' ? Yii::$app->language : null,
                                'codemirror' => null,
                                'toolbar' => Helper::summerNoteToolbars('full'),
                            ],
                            'plugins' => ['fontstyle']
                        ]) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-8">
                        <?= Html::submitButton('<span class="glyphicon glyphicon-ok-sign"></span> ' . Yii::t('podium/view', 'Post Reply'), ['class' => 'btn btn-block btn-primary', 'name' => 'save-button']) ?>
                    </div>
                    <div class="col-sm-4">
                        <?= Html::submitButton('<span class="glyphicon glyphicon-eye-open"></span> ' . Yii::t('podium/view', 'Preview'), ['class' => 'btn btn-block btn-default', 'name' => 'preview-button']) ?>
                    </div>
                </div>
<?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
<br>
<?= $this->render('/elements/forum/_post', ['model' => $previous]) ?>
<br>