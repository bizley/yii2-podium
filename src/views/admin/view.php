<?php

use bizley\podium\components\Helper;
use bizley\podium\models\User;
use cebe\gravatar\Gravatar;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$this->title                   = Yii::t('podium/view', 'Member View');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Administration Dashboard'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Forum Members'), 'url' => ['members']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs('jQuery(\'[data-toggle="tooltip"]\').tooltip()', View::POS_READY, 'bootstrap-tooltip');

echo $this->render('/elements/admin/_navbar', ['active' => 'members']);
?>

<br>
<div class="row">
    <div class="col-sm-9">
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="pull-right">
<?php if ($model->id !== Yii::$app->user->id): ?>
                    <?= Html::a('<span class="glyphicon glyphicon-envelope"></span>', ['messages/new', 'user' => $model->id], ['class' => 'btn btn-default btn-lg', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'Send Message')]); ?>
<?php else: ?>
                    <?= Html::a('<span class="glyphicon glyphicon-envelope"></span>', '#', ['class' => 'btn btn-lg disabled text-muted']); ?>
<?php endif; ?>
<?php if ($model->id !== Yii::$app->user->id): ?>
<?php if ($model->status !== User::STATUS_BANNED): ?>
                    <?= Html::tag('span', Html::button('<span class="glyphicon glyphicon-ban-circle"></span>', ['class' => 'btn btn-danger btn-lg', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'Ban Member')]), ['data-toggle' => 'modal', 'data-target' => '#podiumModalBan']); ?>
<?php else: ?>
                    <?= Html::tag('span', Html::button('<span class="glyphicon glyphicon-ok-circle"></span>', ['class' => 'btn btn-success btn-lg', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'Unban Member')]), ['data-toggle' => 'modal', 'data-target' => '#podiumModalUnBan']); ?>
<?php endif; ?>
<?php else: ?>
                    <?= Html::a('<span class="glyphicon glyphicon-ban-circle"></span>', '#', ['class' => 'btn btn-lg disabled text-muted']); ?>
<?php endif; ?>
<?php if ($model->id !== Yii::$app->user->id): ?>
                    <?= Html::tag('span', Html::button('<span class="glyphicon glyphicon-trash"></span>', ['class' => 'btn btn-danger btn-lg', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'Delete Member')]), ['data-toggle' => 'modal', 'data-target' => '#podiumModalDelete']); ?>
<?php else: ?>
                    <?= Html::a('<span class="glyphicon glyphicon-trash"></span>', '', ['class' => 'btn btn-lg disabled text-muted']); ?>
<?php endif; ?>
                </div>
                <h2>
                    <?= Html::encode($model->getPodiumName()) ?> 
                    <small>
                        <?= Html::encode($model->email) ?> 
                        <?= Helper::roleLabel($model->role) ?>
                        <?= Helper::statusLabel($model->status) ?>
                    </small>
                </h2>
                
                <p><?= Yii::t('podium/view', 'Location') ?>: <?= !empty($model->meta) && !empty($model->meta->location) ? Html::encode($model->meta->location) : '-' ?></p>

<?php if ($model->status == User::STATUS_ACTIVE): ?>
<?php if ($model->role == User::ROLE_MEMBER): ?>
                <p><button class="btn btn-primary" data-toggle="modal" data-target="#podiumModalPromote"><span class="glyphicon glyphicon-open"></span> <?= Yii::t('podium/view', 'Promote {name} to Moderator', ['name' => Html::encode($model->getPodiumName())]) ?></button></p>
<?php elseif ($model->role == User::ROLE_MODERATOR): ?>
                <p><button class="btn btn-warning" data-toggle="modal" data-target="#podiumModalDemote"><span class="glyphicon glyphicon-save"></span> <?= Yii::t('podium/view', 'Demote {name} to Member', ['name' => Html::encode($model->getPodiumName())]) ?></button></p>
<?php endif; ?>
<?php endif; ?>                
                <p><?= Yii::t('podium/view', 'Member since {DATE}', ['DATE' => Yii::$app->formatter->asDatetime($model->created_at, 'long')]) ?> (<?= Yii::$app->formatter->asRelativeTime($model->created_at) ?>)</p>
<?php if ($model->status == User::STATUS_REGISTERED): ?>
                <p><em><?= Yii::t('podium/view', 'The account is awaiting activation.') ?></em></p>
<?php else: ?>
                <p><?= Yii::t('podium/view', 'Last action') ?>: <code><?= Html::encode($model->activity->url) ?></code> <small><?= Yii::t('podium/view', 'IP') ?>: <code><?= Html::encode($model->activity->ip) ?></code> <?= Yii::t('podium/view', 'Date') ?>: <?= Yii::$app->formatter->asDatetime($model->updated_at) ?> (<?= Yii::$app->formatter->asRelativeTime($model->activity->updated_at) ?>)</small></p>
                <p>
                    <a href="" class="btn btn-default"><span class="glyphicon glyphicon-search"></span> <?= Yii::t('podium/view', 'Find all threads started by {name}', ['name' => Html::encode($model->getPodiumName())]) ?></a> 
                    <a href="" class="btn btn-default"><span class="glyphicon glyphicon-search"></span> <?= Yii::t('podium/view', 'Find all posts created by {name}', ['name' => Html::encode($model->getPodiumName())]) ?></a>
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
                'alt' => Yii::t('podium/view', 'Gravatar image'),
                'class' => 'img-circle img-responsive',
            ]]); ?>
<?php elseif (!empty($model->avatar)): ?>
        <img class="img-circle img-responsive" src="/avatars/<?= $model->meta->avatar ?>" alt="<?= Yii::t('podium/view', 'User\'s avatar') ?>">
<?php else: ?>
        <img class="img-circle img-responsive" src="<?= Helper::defaultAvatar() ?>" alt="<?= Yii::t('podium/view', 'Default avatar') ?>">
<?php endif; ?>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="podiumModalDeleteLabel" aria-hidden="true" id="podiumModalDelete">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="podiumModalDeleteLabel"><?= Yii::t('podium/view', 'Delete user') ?></h4>
            </div>
            <div class="modal-body">
                <p><?= Yii::t('podium/view', 'Are you sure you want to delete this user?') ?></p>
                <p><?= Yii::t('podium/view', 'The user can register again using the same name but all previously created posts will not be linked back to him.') ?></p>
                <p><strong><?= Yii::t('podium/view', 'This action can not be undone.') ?></strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= Yii::t('podium/view', 'Cancel') ?></button>
                <a href="<?= Url::to(['delete', 'id' => $model->id]) ?>" class="btn btn-danger"><?= Yii::t('podium/view', 'Delete user') ?></a>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="podiumModalBanLabel" aria-hidden="true" id="podiumModalBan">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="podiumModalBanLabel"><?= Yii::t('podium/view', 'Ban user') ?></h4>
            </div>
            <div class="modal-body">
                <p><?= Yii::t('podium/view', 'Are you sure you want to ban this user?') ?></p>
                <p><?= Yii::t('podium/view', 'The user will not be deleted but will not be able to sign in again.') ?></p>
                <p><strong><?= Yii::t('podium/view', 'You can always unban the user if you change your mind later on.') ?></strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= Yii::t('podium/view', 'Cancel') ?></button>
                <a href="<?= Url::to(['ban', 'id' => $model->id]) ?>" class="btn btn-danger"><?= Yii::t('podium/view', 'Ban user') ?></a>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="podiumModalUnBanLabel" aria-hidden="true" id="podiumModalUnBan">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="podiumModalUnBanLabel"><?= Yii::t('podium/view', 'Unban user') ?></h4>
            </div>
            <div class="modal-body">
                <p><?= Yii::t('podium/view', 'Are you sure you want to unban this user?') ?></p>
                <p><?= Yii::t('podium/view', 'The user will be able to sign in again.') ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= Yii::t('podium/view', 'Cancel') ?></button>
                <a href="<?= Url::to(['ban', 'id' => $model->id]) ?>" class="btn btn-success"><?= Yii::t('podium/view', 'Unban user') ?></a>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="podiumModalPromoteLabel" aria-hidden="true" id="podiumModalPromote">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="podiumModalPromoteLabel"><?= Yii::t('podium/view', 'Promote user') ?></h4>
            </div>
            <div class="modal-body">
                <p><?= Yii::t('podium/view', 'Are you sure you want to promote this user to Moderator?') ?></p>
                <p><?= Yii::t('podium/view', 'You can choose forums for this user to moderate in next step.') ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= Yii::t('podium/view', 'Cancel') ?></button>
                <a href="<?= Url::to(['promote', 'id' => $model->id]) ?>" class="btn btn-success"><?= Yii::t('podium/view', 'Promote user') ?></a>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="podiumModalDemoteLabel" aria-hidden="true" id="podiumModalDemote">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="podiumModalDemoteLabel"><?= Yii::t('podium/view', 'Demote user') ?></h4>
            </div>
            <div class="modal-body">
                <p><?= Yii::t('podium/view', 'Are you sure you want to demote this user to Member?') ?></p>
                <p><?= Yii::t('podium/view', 'All his moderation assignments will be removed.') ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= Yii::t('podium/view', 'Cancel') ?></button>
                <a href="<?= Url::to(['demote', 'id' => $model->id]) ?>" class="btn btn-danger"><?= Yii::t('podium/view', 'Demote user') ?></a>
            </div>
        </div>
    </div>
</div>