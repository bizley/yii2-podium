<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

use bizley\podium\components\Helper;
use bizley\podium\models\User;
use bizley\podium\widgets\Modal;
use cebe\gravatar\Gravatar;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('podium/view', 'Member View');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Members List'), 'url' => ['members/index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs("$('[data-toggle=\"tooltip\"]').tooltip();");
if (!Yii::$app->user->isGuest) {
    $this->registerJs("$('#podiumModalIgnore').on('show.bs.modal', function(e) { var button = $(e.relatedTarget); $('#ignoreUrl').attr('href', button.data('url')); });");
    $this->registerJs("$('#podiumModalUnIgnore').on('show.bs.modal', function(e) { var button = $(e.relatedTarget); $('#unignoreUrl').attr('href', button.data('url')); });");
}

$loggedId = User::loggedId();
$ignored = $friend = false;
if (!Yii::$app->user->isGuest) {
    $ignored = $model->isIgnoredBy($loggedId);
    $friend  = $model->isBefriendedBy($loggedId);
}

?>
<ul class="nav nav-tabs">
    <li role="presentation">
        <a href="<?= Url::to(['members/index']) ?>">
            <span class="glyphicon glyphicon-user"></span> 
            <?= Yii::t('podium/view', 'Members List') ?>
        </a>
    </li>
    <li role="presentation">
        <a href="<?= Url::to(['members/mods']) ?>">
            <span class="glyphicon glyphicon-scissors"></span> 
            <?= Yii::t('podium/view', 'Moderation Team') ?>
        </a>
    </li>
    <li role="presentation" class="active">
        <a href="#">
            <span class="glyphicon glyphicon-eye-open"></span> 
            <?= Yii::t('podium/view', 'Member View') ?>
        </a>
    </li>
</ul>
<br>
<div class="row">
    <div class="col-sm-9">
        <div class="panel panel-default">
            <div class="panel-body">
<?php if (!Yii::$app->user->isGuest): ?>
                <div class="pull-right">
<?php if ($model->id !== $loggedId): ?>
                    <a href="<?= Url::to(['messages/new', 'user' => $model->id]) ?>" class="btn btn-default btn-lg" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Send Message') ?>"><span class="glyphicon glyphicon-envelope"></span></a>
<?php else: ?>
                    <a href="#" class="btn btn-lg disabled text-muted"><span class="glyphicon glyphicon-envelope"></span></a>
<?php endif; ?>
<?php if ($model->id !== $loggedId && $model->role !== User::ROLE_ADMIN): ?>
<?php if (!$friend): ?>
                    <a href="<?= Url::to(['members/friend', 'id' => $model->id]) ?>" class="btn btn-success btn-lg" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Add as a Friend') ?>"><span class="glyphicon glyphicon-plus-sign"></span></a>
<?php else: ?>
                    <a href="<?= Url::to(['members/friend', 'id' => $model->id]) ?>" class="btn btn-warning btn-lg" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Remove Friend') ?>"><span class="glyphicon glyphicon-minus-sign"></span></a>
<?php endif; ?>
<?php if (!$ignored): ?>
                    <span data-toggle="modal" data-target="#podiumModalIgnore" data-url="<?= Url::to(['members/ignore', 'id' => $model->id]) ?>"><button class="btn btn-danger btn-lg" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Ignore Member') ?>"><span class="glyphicon glyphicon-ban-circle"></span></button></span>
<?php else: ?>
                    <span data-toggle="modal" data-target="#podiumModalUnIgnore" data-url="<?= Url::to(['members/ignore', 'id' => $model->id]) ?>"><button class="btn btn-success btn-lg" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Unignore Member') ?>"><span class="glyphicon glyphicon-ok-circle"></span></button></span>
<?php endif; ?>
<?php else: ?>
                    <a href="#" class="btn btn-lg disabled text-muted"><span class="glyphicon glyphicon-ban-circle"></span></a>
<?php endif; ?>
                </div>
<?php if ($ignored): ?>
                <h4 class="text-danger"><?= Yii::t('podium/view', 'You are ignoring this user.') ?></h4>
<?php endif; ?>
<?php if ($friend): ?>
                <h4 class="text-success"><?= Yii::t('podium/view', 'You are friends with this user.') ?></h4>
<?php endif; ?>
<?php endif; ?>
                <h2>
                    <?= Html::encode($model->podiumName) ?> 
                    <small><?= Helper::roleLabel($model->role) ?></small>
                </h2>
                
                <p><?= Yii::t('podium/view', 'Whereabouts') ?>: <?= !empty($model->meta) && !empty($model->meta->location) ? Html::encode($model->meta->location) : '-' ?></p>
                
                <p><?= Yii::t('podium/view', 'Member since {date}', ['date' => Yii::$app->formatter->asDatetime($model->created_at, 'long')]) ?> (<?= Yii::$app->formatter->asRelativeTime($model->created_at) ?>)</p>
<?php if ($model->status != User::STATUS_REGISTERED): ?>
                <p>
                    <a href="<?= Url::to(['members/threads', 'id' => $model->id, 'slug' => $model->podiumSlug]) ?>" class="btn btn-default"><span class="glyphicon glyphicon-search"></span> <?= Yii::t('podium/view', 'Find all threads started by {name}', ['name' => Html::encode($model->podiumName)]) ?></a> 
                    <a href="<?= Url::to(['members/posts', 'id' => $model->id, 'slug' => $model->podiumSlug]) ?>" class="btn btn-default"><span class="glyphicon glyphicon-search"></span> <?= Yii::t('podium/view', 'Find all posts created by {name}', ['name' => Html::encode($model->podiumName)]) ?></a>
                </p>
<?php endif; ?>
            </div>
            <div class="panel-footer">
                <ul class="list-inline">
                    <li><?= Yii::t('podium/view', 'Threads') ?> <span class="badge"><?= $model->threadsCount ?></span></li>
                    <li><?= Yii::t('podium/view', 'Posts') ?> <span class="badge"><?= $model->postsCount ?></span></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-sm-3 hidden-xs">
<?php if (!empty($model->meta->gravatar)): ?>
        <?= Gravatar::widget([
            'email'        => $model->email,
            'defaultImage' => 'identicon',
            'rating'       => 'r',
            'options'      => [
                'alt'   => Yii::t('podium/view', 'Your Gravatar image'),
                'class' => 'img-circle img-responsive',
            ]]); ?>
<?php elseif (!empty($model->meta->avatar)): ?>
        <img class="img-circle img-responsive" src="/avatars/<?= $model->meta->avatar ?>" alt="<?= Yii::t('podium/view', 'Your avatar') ?>">
<?php else: ?>
        <img class="img-circle img-responsive" src="<?= Helper::defaultAvatar() ?>" alt="<?= Yii::t('podium/view', 'Default avatar') ?>">
<?php endif; ?>
    </div>
</div>
<?php if (!Yii::$app->user->isGuest): ?>
<?php Modal::begin([
    'id' => 'podiumModalIgnore',
    'header' => Yii::t('podium/view', 'Ignore user'),
    'footer' => Yii::t('podium/view', 'Ignore user'),
    'footerConfirmOptions' => ['class' => 'btn btn-danger', 'id' => 'ignoreUrl']
 ]) ?>
<p><?= Yii::t('podium/view', 'Are you sure you want to ignore this user?') ?></p>
<p><?= Yii::t('podium/view', 'The user will not be able to send you messages.') ?></p>
<p><strong><?= Yii::t('podium/view', 'You can always unignore the user if you change your mind later on.') ?></strong></p>
<?php Modal::end() ?>
<?php Modal::begin([
    'id' => 'podiumModalUnIgnore',
    'header' => Yii::t('podium/view', 'Unignore user'),
    'footer' => Yii::t('podium/view', 'Unignore user'),
    'footerConfirmOptions' => ['class' => 'btn btn-success', 'id' => 'unignoreUrl']
 ]) ?>
<p><?= Yii::t('podium/view', 'Are you sure you want to ignore this user?') ?></p>
<p><?= Yii::t('podium/view', 'The user will not be able to send you messages.') ?></p>
<p><strong><?= Yii::t('podium/view', 'You can always unignore the user if you change your mind later on.') ?></strong></p>
<?php Modal::end() ?>
<?php endif;
