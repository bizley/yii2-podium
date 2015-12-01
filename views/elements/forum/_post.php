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
use yii\helpers\Html;
use yii\helpers\Url;

$this->registerJs("$('[data-toggle=\"tooltip\"]').tooltip();");
$this->registerJs("$('.podium-quote').click(function (e) { e.preventDefault(); var selection = ''; if (window.getSelection) selection = window.getSelection().toString(); else if (document.selection && document.selection.type != 'Control') selection = document.selection.createRange().text; $(this).parent().find('.quote-selection').val(selection); $(this).parent().find('.quick-quote-form').submit(); });");
$this->registerJs("$('.podium-thumb-up').click(function (e) { e.preventDefault(); var link = $(this); link.removeClass('btn-success').addClass('disabled text-muted'); $.post('" . Url::to(['default/thumb']) . "', {thumb:'up', post:$(this).data('post-id')}, null, 'json').fail(function(){ console.log('Thumb Up Error!'); }).done(function(data){ link.parent().find('.podium-thumb-info').html(data.msg); if (data.error == 0) { var cl = 'default'; if (data.summ > 0) cl = 'success'; else if (data.summ < 0) cl = 'danger'; link.parent().parent().parent().find('.podium-rating').removeClass('label-default label-danger label-success').addClass('label-' + cl).text(data.summ); link.parent().parent().parent().find('.podium-rating-details').text(data.likes + ' / ' + data.dislikes); } link.parent().find('.podium-thumb-down').removeClass('disabled text-muted').addClass('btn-danger'); }); });");
$this->registerJs("$('.podium-thumb-down').click(function (e) { e.preventDefault(); var link = $(this); link.removeClass('btn-danger').addClass('disabled text-muted'); $.post('" . Url::to(['default/thumb']) . "', {thumb:'down', post:$(this).data('post-id')}, null, 'json').fail(function(){ console.log('Thumb Down Error!'); }).done(function(data){ link.parent().find('.podium-thumb-info').html(data.msg); if (data.error == 0) { var cl = 'default'; if (data.summ > 0) cl = 'success'; else if (data.summ < 0) cl = 'danger'; link.parent().parent().parent().find('.podium-rating').removeClass('label-default label-danger label-success').addClass('label-' + cl).text(data.summ); link.parent().parent().parent().find('.podium-rating-details').text(data.likes + ' / ' + data.dislikes); } link.parent().find('.podium-thumb-up').removeClass('disabled text-muted').addClass('btn-success'); }); });");
$this->registerJs("$('.podium-rating').click(function (e) { e.preventDefault(); $('.podium-rating-details').removeClass('hidden'); });");
$this->registerJs("$('.podium-rating-details').click(function (e) { e.preventDefault(); $('.podium-rating-details').addClass('hidden'); });");

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

$loggedId = User::loggedId();

?>
<div class="row" id="post<?= $model->id ?>">
    <div class="col-sm-2 text-center" id="postAvatar<?= $model->id ?>">
        <?= Avatar::widget(['author' => $model->author, 'showName' => false]) ?>
    </div>
    <div class="col-sm-10" id="postContent<?= $model->id ?>">
        <div class="popover right podium">
            <div class="arrow"></div>
            <div class="popover-title">
                <small class="pull-right">
                    <span data-toggle="tooltip" data-placement="top" title="<?= Yii::$app->formatter->asDatetime($model->created_at, 'long') ?>"><?= Yii::$app->formatter->asRelativeTime($model->created_at) ?></span>
<?php if ($model->edited && $model->edited_at): ?>
                    <em>(<?= Yii::t('podium/view', 'Edited') ?> <span data-toggle="tooltip" data-placement="top" title="<?= Yii::$app->formatter->asDatetime($model->edited_at, 'long') ?>"><?= Yii::$app->formatter->asRelativeTime($model->edited_at) ?>)</span></em>
<?php endif; ?>
                    &mdash;
                    <span class="podium-rating label label-<?= $ratingClass ?>" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Rating') ?>"><?= $rating ?></span>
                    <span class="podium-rating-details hidden label label-default">+<?= $model->likes ?> / -<?= $model->dislikes ?></span>
                </small>
                <?= $model->author->podiumTag ?>
                <small>
                    <span class="label label-info" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Number of posts') ?>"><?= $model->author->postsCount ?></span>
                </small>
            </div>
            <div class="popover-content podium-content">
<?php if (isset($parent) && $parent): ?>
                <a href="<?= Url::to(['default/thread', 'cid' => $model->thread->category_id, 'fid' => $model->forum_id, 'id' => $model->thread_id, 'slug' => $model->thread->slug]) ?>"><span class="glyphicon glyphicon-comment"></span> <?= Html::encode($model->thread->name) ?></a><br><br>
<?php endif; ?>
                <?= $model->content ?>
                <div class="podium-action-bar">
                    <?= Html::beginForm(['default/post', 'cid' => $model->thread->category_id, 'fid' => $model->forum_id, 'tid' => $model->thread_id, 'pid' => $model->id], 'post', ['class' => 'quick-quote-form']); ?>
                        <?= Html::hiddenInput('quote', '', ['class' => 'quote-selection']); ?>
                    <?= Html::endForm(); ?>
                    <span class="podium-thumb-info"></span>
<?php if (!Yii::$app->user->isGuest && $model->author_id != $loggedId): ?>
                    <button class="btn btn-primary btn-xs podium-quote" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Reply with quote') ?>"><span class="glyphicon glyphicon-leaf"></span></button>
<?php endif; ?>
<?php if ($model->author_id == $loggedId || User::can(Rbac::PERM_UPDATE_POST, ['item' => $model->thread])): ?>
                    <a href="<?= Url::to(['default/edit', 'cid' => $model->thread->category_id, 'fid' => $model->forum_id, 'tid' => $model->thread_id, 'pid' => $model->id]) ?>" class="btn btn-info btn-xs" data-pjax="0" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Edit post') ?>"><span class="glyphicon glyphicon-edit"></span></a>
<?php endif; ?>
                    <a href="<?= Url::to(['default/show', 'id' => $model->id]) ?>" class="btn btn-default btn-xs" data-pjax="0" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Direct link to this post') ?>"><span class="glyphicon glyphicon-link"></span></a>
<?php if (!Yii::$app->user->isGuest && $model->author_id != $loggedId): ?>
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
<?php if ($model->author_id == $loggedId || User::can(Rbac::PERM_DELETE_POST, ['item' => $model->thread])): ?>
                    <a href="<?= Url::to(['default/deletepost', 'cid' => $model->thread->category_id, 'fid' => $model->forum_id, 'tid' => $model->thread_id, 'pid' => $model->id]) ?>" class="btn btn-danger btn-xs" data-pjax="0" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Delete post') ?>"><span class="glyphicon glyphicon-trash"></span></a>
<?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
