<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */

use bizley\podium\components\Helper;
use bizley\podium\models\User;
use cebe\gravatar\Gravatar;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('podium/view', 'Member View');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Members List'), 'url' => ['members/index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs("$('[data-toggle=\"tooltip\"]').tooltip()");
if (!Yii::$app->user->isGuest) {
    $this->registerJs("$('#podiumModalIgnore').on('show.bs.modal', function(e) { var button = $(e.relatedTarget); $('#ignoreUrl').attr('href', button.data('url')); });");
    $this->registerJs("$('#podiumModalUnIgnore').on('show.bs.modal', function(e) { var button = $(e.relatedTarget); $('#unignoreUrl').attr('href', button.data('url')); });");
}

?>
<ul class="nav nav-tabs">
    <li role="presentation"><a href="<?= Url::to(['members/index']) ?>"><span class="glyphicon glyphicon-user"></span> <?= Yii::t('podium/view', 'Members List') ?></a></li>
    <li role="presentation"><a href="<?= Url::to(['members/mods']) ?>"><span class="glyphicon glyphicon-scissors"></span> <?= Yii::t('podium/view', 'Moderation Team') ?></a></li>
    <li role="presentation" class="active"><a href="#"><span class="glyphicon glyphicon-eye-open"></span> <?= Yii::t('podium/view', 'Member View') ?></a></li>
</ul>
<br>
<div class="row">
    <div class="col-sm-9">
        <div class="panel panel-default">
            <div class="panel-body">
<?php if (!Yii::$app->user->isGuest): ?>
                <div class="pull-right">
<?php if ($model->getId() !== Yii::$app->user->id): ?>
                    <a href="<?= Url::to(['messages/new', 'user' => $model->getId()]) ?>" class="btn btn-default btn-lg" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Send Message') ?>"><span class="glyphicon glyphicon-envelope"></span></a>
<?php else: ?>
                    <a href="#" class="btn btn-lg disabled text-muted"><span class="glyphicon glyphicon-envelope"></span></a>
<?php endif; ?>
<?php if ($model->getId() !== Yii::$app->user->id && $model->getRole() !== User::ROLE_ADMIN): ?>
<?php if (!$model->isIgnoredBy(Yii::$app->user->id)): ?>
                    <span data-toggle="modal" data-target="#podiumModalIgnore" data-url="<?= Url::to(['members/ignore', 'id' => $model->getId()]) ?>"><button class="btn btn-danger btn-lg" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Ignore Member') ?>"><span class="glyphicon glyphicon-ban-circle"></span></button></span>
<?php else: ?>
                    <span data-toggle="modal" data-target="#podiumModalUnIgnore" data-url="<?= Url::to(['members/ignore', 'id' => $model->getId()]) ?>"><button class="btn btn-success btn-lg" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Unignore Member') ?>"><span class="glyphicon glyphicon-ok-circle"></span></button></span>
<?php endif; ?>
<?php else: ?>
                    <a href="#" class="btn btn-lg disabled text-muted"><span class="glyphicon glyphicon-ban-circle"></span></a>
<?php endif; ?>
                </div>
<?php if ($model->isIgnoredBy(Yii::$app->user->id)): ?>
                <h4 class="text-danger"><?= Yii::t('podium/view', 'You are ignoring this user.') ?></h4>
<?php endif; ?>
<?php endif; ?>
                <h2>
                    <?= Html::encode($model->getName()) ?> 
                    <small>
                        <?= Helper::roleLabel($model->getRole()) ?>
                    </small>
                </h2>
                
                <p><?= Yii::t('podium/view', 'Location') ?>: <?= !empty($model->meta) && !empty($model->meta->location) ? Html::encode($model->meta->location) : '-' ?></p>
                
                <p><?= Yii::t('podium/view', 'Member since {DATE}', ['DATE' => Yii::$app->formatter->asDatetime($model->getCreatedAt(), 'long')]) ?> (<?= Yii::$app->formatter->asRelativeTime($model->getCreatedAt()) ?>)</p>
<?php if ($model->getStatus() != User::STATUS_REGISTERED): ?>
                <p>
                    <a href="<?= Url::to(['threads', 'id' => $model->getId(), 'slug' => $model->getSlug()]) ?>" class="btn btn-default"><span class="glyphicon glyphicon-search"></span> <?= Yii::t('podium/view', 'Find all threads started by {name}', ['name' => Html::encode($model->getName())]) ?></a> 
                    <a href="<?= Url::to(['posts', 'id' => $model->getId(), 'slug' => $model->getSlug()]) ?>" class="btn btn-default"><span class="glyphicon glyphicon-search"></span> <?= Yii::t('podium/view', 'Find all posts created by {name}', ['name' => Html::encode($model->getName())]) ?></a>
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
            'email'        => $model->getEmail(),
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