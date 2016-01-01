<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */

use bizley\podium\models\User;
use bizley\podium\rbac\Rbac;
use bizley\podium\widgets\Avatar;
use bizley\podium\widgets\Readers;
use bizley\quill\Quill;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ListView;
use yii\widgets\Pjax;

$this->title = $thread->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Main Forum'), 'url' => ['default/index']];
$this->params['breadcrumbs'][] = ['label' => $category->name, 'url' => ['default/category', 'id' => $category->id, 'slug' => $category->slug]];
$this->params['breadcrumbs'][] = ['label' => $forum->name, 'url' => ['default/forum', 'cid' => $forum->category_id, 'id' => $forum->id, 'slug' => $forum->slug]];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs("$('[data-toggle=\"tooltip\"]').tooltip();");
$this->registerJs("$('.add-subscription').click(function (e) { e.preventDefault(); $.post('" . Url::to(['profile/add', 'id' => $thread->id]) . "', {}, null, 'json').fail(function(){ console.log('Subscription Add Error!'); }).done(function(data){ $('#subsription-status').html(data.msg); }); });");
$this->registerJs("var anchor = window.location.hash; if (anchor.match(/^#post[0-9]+$/)) $(anchor).find('.podium-content').addClass('podium-gradient');");

?>
<?php if (User::can(Rbac::PERM_UPDATE_THREAD, ['item' => $thread])): ?>
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <ul class="list-inline">
                    <li><strong><?= Yii::t('podium/view', 'Moderator options') ?></strong>:</li>
<?php if (User::can(Rbac::PERM_PIN_THREAD, ['item' => $thread])): ?>                    
                    <li><a href="<?= Url::to(['default/pin', 'cid' => $thread->category_id, 'fid' => $thread->forum_id, 'id' => $thread->id, 'slug' => $thread->slug]) ?>" class="btn btn-primary btn-xs"><span class="glyphicon glyphicon-pushpin"></span> <?= $thread->pinned ? Yii::t('podium/view', 'Unpin Thread') : Yii::t('podium/view', 'Pin Thread') ?></a></li>
<?php endif; ?>
<?php if (User::can(Rbac::PERM_LOCK_THREAD, ['item' => $thread])): ?>
                    <li><a href="<?= Url::to(['default/lock', 'cid' => $thread->category_id, 'fid' => $thread->forum_id, 'id' => $thread->id, 'slug' => $thread->slug]) ?>" class="btn btn-primary btn-xs"><span class="glyphicon glyphicon-lock"></span> <?= $thread->locked ? Yii::t('podium/view', 'Unlock Thread') : Yii::t('podium/view', 'Lock Thread') ?></a></li>
<?php endif; ?>
<?php if (User::can(Rbac::PERM_MOVE_THREAD, ['item' => $thread])): ?>
                    <li><a href="<?= Url::to(['default/move', 'cid' => $thread->category_id, 'fid' => $thread->forum_id, 'id' => $thread->id, 'slug' => $thread->slug]) ?>" class="btn btn-primary btn-xs"><span class="glyphicon glyphicon-share-alt"></span> <?= Yii::t('podium/view', 'Move Thread') ?></a></li>
<?php endif; ?>
<?php if (User::can(Rbac::PERM_DELETE_THREAD, ['item' => $thread])): ?>
                    <li><a href="<?= Url::to(['default/delete', 'cid' => $thread->category_id, 'fid' => $thread->forum_id, 'id' => $thread->id, 'slug' => $thread->slug]) ?>" class="btn btn-primary btn-xs"><span class="glyphicon glyphicon-trash"></span> <?= Yii::t('podium/view', 'Delete Thread') ?></a></li>
<?php endif; ?>
<?php if (User::can(Rbac::PERM_MOVE_POST, ['item' => $thread])): ?>
                    <li><a href="<?= Url::to(['default/moveposts', 'cid' => $thread->category_id, 'fid' => $thread->forum_id, 'id' => $thread->id, 'slug' => $thread->slug]) ?>" class="btn btn-primary btn-xs"><span class="glyphicon glyphicon-random"></span> <?= Yii::t('podium/view', 'Move Posts') ?></a></li>
<?php endif; ?>
<?php if (User::can(Rbac::PERM_DELETE_POST, ['item' => $thread])): ?>
                    <li><a href="<?= Url::to(['default/deleteposts', 'cid' => $thread->category_id, 'fid' => $thread->forum_id, 'id' => $thread->id, 'slug' => $thread->slug]) ?>" class="btn btn-primary btn-xs"><span class="glyphicon glyphicon-remove"></span> <?= Yii::t('podium/view', 'Delete Posts') ?></a></li>
<?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-sm-12 text-right">
        <ul class="list-inline">
<?php if (Yii::$app->user->isGuest): ?>
            <li><a href="<?= Url::to(['account/login']) ?>" class="btn btn-primary btn-sm"><?= Yii::t('podium/view', 'Sign in to reply') ?></a></li>
            <li><a href="<?= Url::to(['account/register']) ?>" class="btn btn-success btn-sm"><?= Yii::t('podium/view', 'Register new account') ?></a></li>
<?php else: ?>
<?php if (User::can(Rbac::PERM_CREATE_THREAD)): ?>            
            <li><a href="<?= Url::to(['default/new-thread', 'cid' => $category->id, 'fid' => $thread->forum_id]) ?>" class="btn btn-primary btn-sm"><span class="glyphicon glyphicon-plus"></span> <?= Yii::t('podium/view', 'Create new thread') ?></a></li>
<?php endif; ?>
            <li><a href="<?= Url::to(['default/unread-posts']) ?>" class="btn btn-info btn-sm"><span class="glyphicon glyphicon-flash"></span> <?= Yii::t('podium/view', 'Unread posts') ?></a></li>
<?php endif; ?>
        </ul>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
<?php if ($thread->subscription): ?>
                    <a href="<?= Url::to(['profile/subscriptions']) ?>" class="btn btn-warning btn-xs pull-right" data-toggle="tooltip" data-placement="left" title="<?= Yii::t('podium/view', 'You subscribe this thread') ?>"><span class="glyphicon glyphicon-star"></span></a>
<?php elseif (!Yii::$app->user->isGuest): ?>
                    <small id="subsription-status" class="pull-right"><button class="add-subscription btn btn-success btn-xs"><span class="glyphicon glyphicon-star-empty"></span> <?= Yii::t('podium/view', 'Subscribe to this thread') ?></button></small>
<?php endif; ?>
                    <?= Html::encode($thread->name) ?>
                </h3>
            </div>
        </div>
    </div>
</div><br>

<?php Pjax::begin(); ?>
<?= ListView::widget([
    'dataProvider'     => $dataProvider,
    'itemView'         => '/elements/forum/_post',
    'summary'          => '',
    'emptyText'        => Yii::t('podium/view', 'No posts have been added yet.'),
    'emptyTextOptions' => ['tag' => 'h3', 'class' => 'text-muted'],
    'pager'            => ['options' => ['class' => 'pagination pull-right']]
]); ?> 
<?php Pjax::end(); ?>

<?php if ($thread->locked == 0 || ($thread->locked == 1 && User::can(Rbac::PERM_UPDATE_THREAD, ['item' => $thread]))): ?>
<?php if (!Yii::$app->user->isGuest): ?>
<br>
<div class="row">
    <div class="col-sm-12 text-right">
        <a href="<?= Url::to(['default/post', 'cid' => $category->id, 'fid' => $forum->id, 'tid' => $thread->id]) ?>" class="btn btn-primary btn-lg"><span class="glyphicon glyphicon-leaf"></span> New Reply</a>
    </div>
</div>
<br>
<div class="row">
    <div class="col-sm-2 text-center">
        <?= Avatar::widget(['author' => User::findMe(), 'showName' => false]) ?>
    </div>
    <div class="col-sm-10">
        <div class="popover right podium">
            <div class="arrow"></div>
            <div class="popover-title">
                <small class="pull-right"><?= Html::tag('span', Yii::t('podium/view', 'In a while'), ['data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'As soon as you click Post Reply')]); ?></small>
                <strong><?= Yii::t('podium/view', 'Post Quick Reply') ?></strong> <?= User::findMe()->podiumTag ?>
            </div>
            <div class="popover-content podium-content">
                <?php $form = ActiveForm::begin(['id' => 'new-quick-post-form', 'action' => ['post', 'cid' => $category->id, 'fid' => $forum->id, 'tid' => $thread->id]]); ?>
                    <div class="row">
                        <div class="col-sm-12">
                            <?= $form->field($model, 'content')->label(false)->widget(Quill::className(), ['toolbar' => 'basic', 'options' => ['style' => 'height:100px']]) ?>
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
    <div class="col-sm-10 col-sm-offset-2 text-center">
        <div class="panel panel-danger">
            <div class="panel-heading">
                <h3 class="panel-title"><span class="glyphicon glyphicon-lock"></span> <?= Yii::t('podium/view', 'This thread is locked.') ?></h3>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<br>
<div class="panel panel-default">
    <div class="panel-body small">
        <ul class="list-inline pull-right">
            <li><a href="<?= Url::to(['default/index']) ?>" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Go to the main page') ?>"><span class="glyphicon glyphicon-home"></span></a></li>
            <li><a href="#top" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Go to the top') ?>"><span class="glyphicon glyphicon-arrow-up"></span></a></li>
        </ul>
        <?= Readers::widget(['what' => 'topic']) ?>
    </div>
</div>
