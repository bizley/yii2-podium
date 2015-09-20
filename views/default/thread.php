<?php

use bizley\podium\components\Helper;
use bizley\podium\components\PodiumUser;
use bizley\podium\widgets\Avatar;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ListView;
use yii\widgets\Pjax;
use Zelenin\yii\widgets\Summernote\Summernote;

$this->registerJs('jQuery(\'.add-subscription\').click(function(e){
    e.preventDefault();
    jQuery.post(\'' . Url::to(['profile/add', 'id' => $thread->id]) . '\', {}, null, \'json\').
        fail(function(){ console.log(\'Subscription Add Error!\'); }).
        done(function(data){ jQuery(\'#subsription-status\').html(data.msg); });
})', View::POS_READY, 'add-subscription');
$this->registerJs('var anchor=window.location.hash; if (anchor.match(/^#post[0-9]+$/)) jQuery(anchor).find(\'.podium-content\').addClass(\'podium-gradient\');', View::POS_READY, 'anchor-marked');

$this->title                   = Html::encode($thread->name);
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Main Forum'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Html::encode($category->name), 'url' => ['category', 'id' => $category->id, 'slug' => $category->slug]];
$this->params['breadcrumbs'][] = ['label' => Html::encode($forum->name), 'url' => ['forum', 'cid' => $forum->category_id, 'id' => $forum->id, 'slug' => $forum->slug]];
$this->params['breadcrumbs'][] = $this->title;

$podiumUser = new PodiumUser;
?>

<?php if (Yii::$app->user->can('updatePodiumThread', ['item' => $thread])): ?>
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-warning">
            <div class="panel-heading">
                <ul class="list-inline">
                    <li><strong><?= Yii::t('podium/view', 'Moderator options') ?></strong>:</li>
                    <li><a href="<?= Url::to(['pin', 'cid' => $thread->category_id, 'fid' => $thread->forum_id, 'id' => $thread->id, 'slug' => $thread->slug]) ?>"><span class="glyphicon glyphicon-pushpin"></span> <?= Yii::t('podium/view', $thread->pinned ? 'Unpin Thread' : 'Pin Thread') ?></a></li>
                    <li><a href="<?= Url::to(['lock', 'cid' => $thread->category_id, 'fid' => $thread->forum_id, 'id' => $thread->id, 'slug' => $thread->slug]) ?>"><span class="glyphicon glyphicon-lock"></span> <?= Yii::t('podium/view', $thread->locked ? 'Unlock Thread' : 'Lock Thread') ?></a></li>
                    <li><a href="<?= Url::to(['move', 'cid' => $thread->category_id, 'fid' => $thread->forum_id, 'id' => $thread->id, 'slug' => $thread->slug]) ?>"><span class="glyphicon glyphicon-share-alt"></span> <?= Yii::t('podium/view', 'Move Thread') ?></a></li>
                    <li><a href="<?= Url::to(['delete', 'cid' => $thread->category_id, 'fid' => $thread->forum_id, 'id' => $thread->id, 'slug' => $thread->slug]) ?>"><span class="glyphicon glyphicon-trash"></span> <?= Yii::t('podium/view', 'Delete Thread') ?></a></li>
                    <li><a href="<?= Url::to(['moveposts', 'cid' => $thread->category_id, 'fid' => $thread->forum_id, 'id' => $thread->id, 'slug' => $thread->slug]) ?>"><span class="glyphicon glyphicon-random"></span> <?= Yii::t('podium/view', 'Move Posts') ?></a></li>
                    <li><a href="<?= Url::to(['deleteposts', 'cid' => $thread->category_id, 'fid' => $thread->forum_id, 'id' => $thread->id, 'slug' => $thread->slug]) ?>"><span class="glyphicon glyphicon-remove"></span> <?= Yii::t('podium/view', 'Delete Posts') ?></a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (Yii::$app->user->isGuest): ?>
<div class="row">
    <div class="col-sm-12 text-right">
        <a href="<?= Url::to(['account/login']) ?>" class="btn btn-primary"><?= Yii::t('podium/view', 'Sign in to reply') ?></a>
        <a href="<?= Url::to(['account/register']) ?>" class="btn btn-success"><?= Yii::t('podium/view', 'Register new account') ?></a>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-sm-12">
        <h4>
<?php if ($thread->subscription): ?>
            <small><a href="<?= Url::to(['profile/subscriptions']) ?>"><span class="label label-info pull-right"><span class="glyphicon glyphicon-star"></span> <?= Yii::t('podium/view', 'You subscribe this thread') ?></span></a></small>
<?php elseif (!Yii::$app->user->isGuest): ?>
            <small id="subsription-status" class="pull-right"><button class="add-subscription btn btn-success btn-xs"><span class="glyphicon glyphicon-star-empty"></span> <?= Yii::t('podium/view', 'Subscribe to this thread') ?></button></small>
<?php endif; ?>
            <?= Html::encode($thread->name) ?>
        </h4>
    </div>
</div><br>

<?php Pjax::begin();
echo ListView::widget([
    'dataProvider' => $dataProvider,
    'itemView' => '/elements/forum/_post',
    'summary' => '',
    'emptyText' => Yii::t('podium/view', 'No posts have been added yet.'),
    'emptyTextOptions' => ['tag' => 'h3', 'class' => 'text-muted'],
    'pager' => ['options' => ['class' => 'pagination pull-right']]
]); 
Pjax::end();
?>

<?php if ($thread->locked == 0 || ($thread->locked == 1 && Yii::$app->user->can('updatePodiumThread', ['item' => $thread]))): ?>
<?php if (!Yii::$app->user->isGuest): ?>
<br>
<div class="row">
    <div class="col-sm-12 text-right">
        <a href="<?= Url::to(['post', 'cid' => $category->id, 'fid' => $forum->id, 'tid' => $thread->id]) ?>" class="btn btn-primary btn-lg"><span class="glyphicon glyphicon-leaf"></span> New Reply</a>
    </div>
</div>
<br>
<div class="row">
    <div class="col-sm-2 text-center">
        <?= Avatar::widget(['author' => $podiumUser, 'showName' => false]) ?>
    </div>
    <div class="col-sm-10">
        <div class="popover right podium">
            <div class="arrow"></div>
            <div class="popover-title">
                <small class="pull-right"><?= Html::tag('span', Yii::t('podium/view', 'In a while'), ['data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'As soon as you click Post Reply')]); ?></small>
                <strong><?= Yii::t('podium/view', 'Post Quick Reply') ?></strong> <?= $podiumUser->getPodiumTag() ?>
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
<?php if (!$thread->subscription): ?>
                <div class="row">
                    <div class="col-sm-12">
                        <?= $form->field($model, 'subscribe')->checkbox()->label(Yii::t('podium/view', 'Subscribe to this thread')) ?>
                    </div>
                </div>
<?php else: ?>
                <?= Html::activeHiddenInput($model, 'subscribe') ?>
<?php endif; ?>
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
<?php else: ?>
<div class="row">
    <br>
    <div class="col-sm-12 text-right">
        <a href="<?= Url::to(['account/login']) ?>" class="btn btn-primary"><?= Yii::t('podium/view', 'Sign in to reply') ?></a>
        <a href="<?= Url::to(['account/register']) ?>" class="btn btn-success"><?= Yii::t('podium/view', 'Register new account') ?></a>
    </div>
</div>
<?php endif; ?>
<?php else: ?>
<div class="row">
    <div class="col-sm-12 text-right">
        <h4><span class="glyphicon glyphicon-lock"></span> <?= Yii::t('podium/view', 'This thread is locked.') ?></h4>
    </div>
</div>
<?php endif; ?>
<br>