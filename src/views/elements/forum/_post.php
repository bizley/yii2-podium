<?php

use bizley\podium\widgets\Avatar;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$this->registerJs('jQuery(\'[data-toggle="tooltip"]\').tooltip();', View::POS_READY, 'bootstrap-tooltip');
$this->registerJs('jQuery(\'.podium-quote\').click(function(e){
    e.preventDefault();
    var selection = \'\';
    if (window.getSelection) selection = window.getSelection().toString();
    else if (document.selection && document.selection.type != \'Control\') selection = document.selection.createRange().text;
    jQuery(this).parent().find(\'.quote-selection\').val(selection);
    jQuery(this).parent().find(\'.quick-quote-form\').submit();
})', View::POS_READY, 'podium-quote');
$this->registerJs('jQuery(\'.podium-thumb-up\').click(function(e){
    e.preventDefault(); var $link = jQuery(this);
    $link.removeClass(\'btn-success\').addClass(\'disabled text-muted\');
    jQuery.post(\'' . Url::to(['default/thumb']) . '\', {thumb:\'up\',post:jQuery(this).data(\'post-id\')}, null, \'json\').
        fail(function(){ console.log(\'Thumb Up Error!\'); }).
        done(function(data){ 
            $link.parent().find(\'.podium-thumb-info\').html(data.msg);
            if (data.error==0) {
                var cl=\'default\';
                if (data.summ>0) cl=\'success\'; else if (data.summ<0) cl=\'danger\';
                $link.parent().parent().parent().find(\'.podium-rating\').removeClass(\'label-default label-danger label-success\').addClass(\'label-\'+cl).text(data.summ);
                $link.parent().parent().parent().find(\'.podium-rating-details\').text(data.likes+\' / \'+data.dislikes);
            }
            $link.parent().find(\'.podium-thumb-down\').removeClass(\'disabled text-muted\').addClass(\'btn-danger\'); 
        });
})', View::POS_READY, 'podium-thumb-up');
$this->registerJs('jQuery(\'.podium-thumb-down\').click(function(e){
    e.preventDefault(); var $link = jQuery(this);
    $link.removeClass(\'btn-danger\').addClass(\'disabled text-muted\');
    jQuery.post(\'' . Url::to(['default/thumb']) . '\', {thumb:\'down\',post:jQuery(this).data(\'post-id\')}, null, \'json\').
        fail(function(){ console.log(\'Thumb Down Error!\'); }).
        done(function(data){ 
            $link.parent().find(\'.podium-thumb-info\').html(data.msg);
            if (data.error==0) {
                var cl=\'default\';
                if (data.summ>0) cl=\'success\'; else if (data.summ<0) cl=\'danger\';
                $link.parent().parent().parent().find(\'.podium-rating\').removeClass(\'label-default label-danger label-success\').addClass(\'label-\'+cl).text(data.summ);
                $link.parent().parent().parent().find(\'.podium-rating-details\').text(data.likes+\' / \'+data.dislikes);
            }
            $link.parent().find(\'.podium-thumb-up\').removeClass(\'disabled text-muted\').addClass(\'btn-success\'); 
        });
})', View::POS_READY, 'podium-thumb-down');
$this->registerJs('jQuery(\'.podium-rating\').click(function(e){ e.preventDefault(); jQuery(\'.podium-rating-details\').removeClass(\'hidden\'); })', View::POS_READY, 'podium-rating');
$this->registerJs('jQuery(\'.podium-rating-details\').click(function(e){ e.preventDefault(); jQuery(\'.podium-rating-details\').addClass(\'hidden\'); })', View::POS_READY, 'podium-rating-hide');

if (!Yii::$app->user->isGuest) {
    $model->markSeen();
}

$rating = $model->likes - $model->dislikes;
$ratingClass = 'default';
if ($rating > 0) {
    $ratingClass = 'success';
    $rating      = '+' . $rating;
}
elseif ($rating < 0) {
    $ratingClass = 'danger';
}

?><div class="row" id="post<?= $model->id ?>">
    <div class="col-sm-2 text-center" id="postAvatar<?= $model->id ?>">
        <?= Avatar::widget(['author' => $model->user, 'showName' => false]) ?>
    </div>
    <div class="col-sm-10" id="postContent<?= $model->id ?>">
        <div class="popover right podium">
            <div class="arrow"></div>
            <div class="popover-title">
                <small class="pull-right">
                    <?= Html::tag('span', Yii::$app->formatter->asRelativeTime($model->created_at), ['data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::$app->formatter->asDatetime($model->created_at, 'long')]); ?>
<?php if ($model->edited && $model->edited_at): ?>
                    <em>(<?= Yii::t('podium/view', 'Edited') ?> <?= Html::tag('span', Yii::$app->formatter->asRelativeTime($model->edited_at), ['data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::$app->formatter->asDatetime($model->edited_at, 'long')]); ?>)</em>
<?php endif; ?>
                    &mdash;
                    <span class="podium-rating label label-<?= $ratingClass ?>" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Rating') ?>"><?= $rating ?></span>
                    <span class="podium-rating-details hidden label label-default">+<?= $model->likes ?> / -<?= $model->dislikes ?></span>
                </small>
                <?= $model->user->getPodiumTag() ?>
                <small>
                    <span class="label label-info" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Number of posts') ?>"><?= $model->user->getPostsCount() ?></span>
                </small>
            </div>
            <div class="popover-content podium-content">
<?php if (isset($parent)): ?>
                
<?php endif; ?>
                <?= $model->content ?>
                <div class="podium-action-bar">
                    <?= Html::beginForm(['default/post', 'cid' => $model->thread->category_id, 'fid' => $model->forum_id, 'tid' => $model->thread_id, 'pid' => $model->id], 'post', ['class' => 'quick-quote-form']); ?>
                        <?= Html::hiddenInput('quote', '', ['class' => 'quote-selection']); ?>
                    <?= Html::endForm(); ?>
                    <span class="podium-thumb-info"></span>
<?php if (!Yii::$app->user->isGuest && $model->author_id != Yii::$app->user->id): ?>
                    <button class="btn btn-primary btn-xs podium-quote" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Reply with quote') ?>"><span class="glyphicon glyphicon-leaf"></span></button>
<?php endif; ?>
<?php if ($model->author_id == Yii::$app->user->id || Yii::$app->user->can('updatePost', ['item' => $model->thread])): ?>
                    <a href="<?= Url::to(['default/edit', 'cid' => $model->thread->category_id, 'fid' => $model->forum_id, 'tid' => $model->thread_id, 'pid' => $model->id]) ?>" class="btn btn-info btn-xs" data-pjax="0" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Edit post') ?>"><span class="glyphicon glyphicon-edit"></span></a>
<?php endif; ?>
                    <a href="<?= Url::to(['default/show', 'id' => $model->id]) ?>" class="btn btn-default btn-xs" data-pjax="0" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Direct link to this post') ?>"><span class="glyphicon glyphicon-link"></span></a>
<?php if (!Yii::$app->user->isGuest && $model->author_id != Yii::$app->user->id): ?>
<?php if ($model->thumb && $model->thumb->thumb == 1): ?>
                    <a href="#" class="btn btn-xs disabled text-muted podium-thumb-up" data-post-id="<?= $model->id ?>" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Thumb up') ?>"><span class="glyphicon glyphicon-thumbs-up"></span></a>
<?php else: ?>
                    <a href="#" class="btn btn-success btn-xs podium-thumb-up" data-post-id="<?= $model->id ?>" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Thumb up') ?>"><span class="glyphicon glyphicon-thumbs-up"></span></a>
<?php endif; ?>
<?php if ($model->thumb && $model->thumb->thumb == -1): ?>
                    <a href="#" class="btn btn-xs disabled text-muted podium-thumb-down" data-post-id="<?= $model->id ?>" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Thumb down') ?>"><span class="glyphicon glyphicon-thumbs-down"></span></a>
<?php else: ?>
                    <a href="#" class="btn btn-danger btn-xs podium-thumb-down" data-post-id="<?= $model->id ?>" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Thumb down') ?>"><span class="glyphicon glyphicon-thumbs-down"></span></a>
<?php endif; ?>
                    <a href="<?= Url::to(['default/report', 'cid' => $model->thread->category_id, 'fid' => $model->forum_id, 'tid' => $model->thread_id, 'pid' => $model->id, 'slug' => $model->thread->slug]) ?>" class="btn btn-warning btn-xs" data-pjax="0" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Report post') ?>"><span class="glyphicon glyphicon-flag"></span></a>
<?php endif; ?>
<?php if ($model->author_id == Yii::$app->user->id || Yii::$app->user->can('deletePost', ['item' => $model->thread])): ?>
                    <a href="<?= Url::to(['default/deletepost', 'cid' => $model->thread->category_id, 'fid' => $model->forum_id, 'tid' => $model->thread_id, 'pid' => $model->id]) ?>" class="btn btn-danger btn-xs" data-pjax="0" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Delete post') ?>"><span class="glyphicon glyphicon-trash"></span></a>
<?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
