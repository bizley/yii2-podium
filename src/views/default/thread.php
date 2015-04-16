<?php

use bizley\podium\components\Helper;
use bizley\podium\widgets\Avatar;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ListView;
use Zelenin\yii\widgets\Summernote\Summernote;

$this->title                   = Html::encode($thread->name);
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Main Forum'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Html::encode($category->name), 'url' => ['category', 'id' => $category->id, 'slug' => $category->slug]];
$this->params['breadcrumbs'][] = ['label' => Html::encode($forum->name), 'url' => ['forum', 'cid' => $forum->category_id, 'id' => $forum->id, 'slug' => $forum->slug]];
$this->params['breadcrumbs'][] = $this->title;

echo ListView::widget([
    'dataProvider' => $dataProvider,
    'itemView' => '/elements/forum/_post',
    'viewParams' => ['category' => $category->id, 'slug' => $thread->slug],
    'summary' => '',
    'emptyText' => Yii::t('podium/view', 'No posts have been added yet.'),
    'emptyTextOptions' => ['tag' => 'h3', 'class' => 'text-muted'],
    'pager' => ['options' => ['class' => 'pagination pull-right']]
]); ?>

<br>
<div class="row">
    <div class="col-sm-12 text-right">
        <a href="<?= Url::to(['post', 'cid' => $category->id, 'fid' => $forum->id, 'tid' => $thread->id]) ?>" class="btn btn-primary btn-lg"><span class="glyphicon glyphicon-leaf"></span> New Reply</a>
    </div>
</div>
<br>
<div class="row">
    <div class="col-sm-2 text-center">
        <?= Avatar::widget(['author' => Yii::$app->user->getIdentity(), 'showName' => false]) ?>
    </div>
    <div class="col-sm-10">
        <div class="popover right podium">
            <div class="arrow"></div>
            <div class="popover-title">
                <small class="pull-right"><?= Html::tag('span', Yii::t('podium/view', 'In a while'), ['data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'As soon as you click Post Reply')]); ?></small>
                <strong><?= Yii::t('podium/view', 'Post Quick Reply') ?></strong> <?= Yii::$app->user->getIdentity()->getPodiumTag() ?>
            </div>
            <div class="popover-content podium-content">
<?php $form = ActiveForm::begin(['id' => 'new-quick-post-form', 'action' => ['post', 'cid' => $category->id, 'fid' => $forum->id, 'tid' => $thread->id]]); ?>
                <div class="row">
                    <div class="col-sm-12">
                        <?= $form->field($model, 'content')->label(false)->widget(Summernote::className(), [
                            'clientOptions' => [
                                'height' => '100',
                                'lang' => Yii::$app->language != 'en-US' ? Yii::$app->language : null,
                                'codemirror' => null,
                                'toolbar' => Helper::summerNoteToolbars(),
                            ],
                        ]) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-8">
                        <?= Html::submitButton('<span class="glyphicon glyphicon-ok-sign"></span> ' . Yii::t('podium/view', 'Post Quick Reply'), ['class' => 'btn btn-block btn-primary', 'name' => 'save-button']) ?>
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