<?php

use bizley\podium\components\Helper;
use bizley\podium\models\User;
use cebe\gravatar\Gravatar;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$this->title                   = Yii::t('podium/view', 'Member View');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Members List'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs('jQuery(\'[data-toggle="tooltip"]\').tooltip()', View::POS_READY, 'bootstrap-tooltip');
if (!Yii::$app->user->isGuest) {
    $this->registerJs('jQuery(\'#podiumModalIgnore\').on(\'show.bs.modal\', function(e) {
    var button = jQuery(e.relatedTarget);
    jQuery(\'#ignoreUrl\').attr(\'href\', button.data(\'url\'));
});', View::POS_READY, 'bootstrap-modal-ban');
    $this->registerJs('jQuery(\'#podiumModalUnIgnore\').on(\'show.bs.modal\', function(e) {
    var button = jQuery(e.relatedTarget);
    jQuery(\'#unignoreUrl\').attr(\'href\', button.data(\'url\'));
});', View::POS_READY, 'bootstrap-modal-unban');
}

echo Html::beginTag('ul', ['class' => 'nav nav-tabs']);
echo Html::tag('li', Html::a('<span class="glyphicon glyphicon-user"></span> ' . Yii::t('podium/view', 'Members List'), ['index']), ['role' => 'presentation']);
echo Html::tag('li', Html::a('<span class="glyphicon glyphicon-scissors"></span> ' . Yii::t('podium/view', 'Moderation Team'), ['mods']), ['role' => 'presentation']);
echo Html::tag('li', Html::a('<span class="glyphicon glyphicon-eye-open"></span> ' . Yii::t('podium/view', 'Member View'), ''), ['role' => 'presentation', 'class' => 'active']);
echo Html::endTag('ul'); ?>

<br>
<div class="row">
    <div class="col-sm-9">
        <div class="panel panel-default">
            <div class="panel-body">
<?php if (!Yii::$app->user->isGuest): ?>
                <div class="pull-right">
<?php if ($model->id !== Yii::$app->user->id): ?>
                    <?= Html::a('<span class="glyphicon glyphicon-envelope"></span>', ['messages/new', 'user' => $model->id], ['class' => 'btn btn-default btn-lg', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'Send Message')]); ?>
<?php else: ?>
                    <?= Html::a('<span class="glyphicon glyphicon-envelope"></span>', '#', ['class' => 'btn btn-lg disabled text-muted']); ?>
<?php endif; ?>
<?php if ($model->id !== Yii::$app->user->id && $model->role !== User::ROLE_ADMIN): ?>
<?php if (!$model->isIgnoredBy(Yii::$app->user->id)): ?>
                    <?= Html::tag('span', Html::button('<span class="glyphicon glyphicon-ban-circle"></span>', ['class' => 'btn btn-danger btn-lg', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'Ignore Member')]), ['data-toggle' => 'modal', 'data-target' => '#podiumModalIgnore', 'data-url' => Url::to(['ignore', 'id' => $model->id])]); ?>
<?php else: ?>
                    <?= Html::tag('span', Html::button('<span class="glyphicon glyphicon-ok-circle"></span>', ['class' => 'btn btn-success btn-lg', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'Unignore Member')]), ['data-toggle' => 'modal', 'data-target' => '#podiumModalUnIgnore', 'data-url' => Url::to(['ignore', 'id' => $model->id])]); ?>
<?php endif; ?>
<?php else: ?>
                    <?= Html::a('<span class="glyphicon glyphicon-ban-circle"></span>', '#', ['class' => 'btn btn-lg disabled text-muted']); ?>
<?php endif; ?>
                </div>
<?php if ($model->isIgnoredBy(Yii::$app->user->id)): ?>
                <h4 class="text-danger"><?= Yii::t('podium/view', 'You are ignoring this user.') ?></h4>
<?php endif; ?>
<?php endif; ?>
                <h2>
                    <?= Html::encode($model->getPodiumName()) ?> 
                    <small>
                        <?= Helper::roleLabel($model->role) ?>
                    </small>
                </h2>
                
                <p><?= Yii::t('podium/view', 'Location') ?>: <?= !empty($model->meta) && !empty($model->meta->location) ? Html::encode($model->meta->location) : '-' ?></p>
                
                <p><?= Yii::t('podium/view', 'Member since {DATE}', ['DATE' => Yii::$app->formatter->asDatetime($model->created_at, 'long')]) ?> (<?= Yii::$app->formatter->asRelativeTime($model->created_at) ?>)</p>
<?php if ($model->status != User::STATUS_REGISTERED): ?>
                <p>
                    <a href="<?= Url::to(['threads', 'id' => $model->id, 'slug' => $model->slug]) ?>" class="btn btn-default"><span class="glyphicon glyphicon-search"></span> <?= Yii::t('podium/view', 'Find all threads started by {name}', ['name' => Html::encode($model->getPodiumName())]) ?></a> 
                    <a href="<?= Url::to(['posts', 'id' => $model->id, 'slug' => $model->slug]) ?>" class="btn btn-default"><span class="glyphicon glyphicon-search"></span> <?= Yii::t('podium/view', 'Find all posts created by {name}', ['name' => Html::encode($model->getPodiumName())]) ?></a>
                </p>
<?php endif; ?>
            </div>
            <div class="panel-footer">
                <ul class="list-inline">
                    <li><?= Yii::t('podium/view', 'Threads') ?> <span class="badge"><?= $model->getThreadsCount() ?></span></li>
                    <li><?= Yii::t('podium/view', 'Posts') ?> <span class="badge"><?= $model->getPostsCount() ?></span></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
<?php if (!empty($model->meta->gravatar)): ?>
        <?= Gravatar::widget([
            'email' => $model->email,
            'defaultImage' => 'identicon',
            'rating' => 'r',
            'options' => [
                'alt' => Yii::t('podium/view', 'Your Gravatar image'),
                'class' => 'img-circle img-responsive',
            ]]); ?>
<?php elseif (!empty($model->avatar)): ?>
        <img class="img-circle img-responsive" src="/avatars/<?= $model->meta->avatar ?>" alt="<?= Yii::t('podium/view', 'Your avatar') ?>">
<?php else: ?>
        <img class="img-circle img-responsive" src="<?= Helper::defaultAvatar() ?>" alt="<?= Yii::t('podium/view', 'Default avatar') ?>">
<?php endif; ?>
    </div>
</div>
<?php if (!Yii::$app->user->isGuest): ?>
<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="podiumModalIgnoreLabel" aria-hidden="true" id="podiumModalIgnore">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="podiumModalIgnoreLabel"><?= Yii::t('podium/view', 'Ignore user') ?></h4>
            </div>
            <div class="modal-body">
                <p><?= Yii::t('podium/view', 'Are you sure you want to ignore this user?') ?></p>
                <p><?= Yii::t('podium/view', 'The user will not be able to send you messages.') ?></p>
                <p><strong><?= Yii::t('podium/view', 'You can always unignore the user if you change your mind later on.') ?></strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= Yii::t('podium/view', 'Cancel') ?></button>
                <a href="#" id="ignoreUrl" class="btn btn-danger"><?= Yii::t('podium/view', 'Ignore user') ?></a>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="podiumModalUnIgnoreLabel" aria-hidden="true" id="podiumModalUnIgnore">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="podiumModalUnIgnoreLabel"><?= Yii::t('podium/view', 'Unignore user') ?></h4>
            </div>
            <div class="modal-body">
                <p><?= Yii::t('podium/view', 'Are you sure you want to unignore this user?') ?></p>
                <p><?= Yii::t('podium/view', 'The user will be able to send you messages again.') ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= Yii::t('podium/view', 'Cancel') ?></button>
                <a href="#" id="unignoreUrl" class="btn btn-success"><?= Yii::t('podium/view', 'Unignore user') ?></a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>