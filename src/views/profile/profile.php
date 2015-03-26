<?php

use yii\helpers\Html;
use bizley\podium\components\Helper;
use cebe\gravatar\Gravatar;

$this->title                   = Yii::t('podium/view', 'My Profile');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="row">
    <div class="col-sm-3">
        <?= $this->render('/elements/profile/_navbar', ['active' => 'profile']) ?>
    </div>
    <div class="col-sm-9">
        <div class="panel panel-default">
            <div class="panel-body">
<?php if (!empty($model->meta->gravatar)): ?>
                <?= Gravatar::widget([
                    'email' => $model->email,
                    'defaultImage' => 'identicon',
                    'rating' => 'r',
                    'options' => [
                        'alt' => Html::encode($model->getPodiumName()),
                        'class' => 'podium-avatar img-circle img-responsive pull-right',
                ]]); ?>
<?php elseif (!empty($model->meta->avatar)): ?>
                <img class="podium-avatar img-circle img-responsive pull-right" src="/avatars/<?= $model->meta->avatar ?>" alt="<?= Html::encode($model->getPodiumName()) ?>">
<?php else: ?>
                <img class="podium-avatar img-circle img-responsive pull-right" src="<?= Helper::defaultAvatar() ?>" alt="<?= Html::encode($model->getPodiumName()) ?>">
<?php endif; ?>
                <h2>
                    <?= Html::encode($model->getPodiumName()) ?> 
                    <small>
                        <?= Html::encode($model->email) ?> 
                        <?= Helper::roleLabel($model->role) ?>
                    </small>
                </h2>
                <p><?= Yii::t('podium/view', 'Member since {DATE}', ['DATE' => Yii::$app->formatter->asDatetime($model->created_at, 'long')]) ?> (<?= Yii::$app->formatter->asRelativeTime($model->created_at) ?>)</p>
                <p>
                    <a href="" class="btn btn-default"><span class="glyphicon glyphicon-search"></span> <?= Yii::t('podium/view', 'Show all threads started by me') ?></a> 
                    <a href="" class="btn btn-default"><span class="glyphicon glyphicon-search"></span> <?= Yii::t('podium/view', 'Show all posts created by me') ?></a>
                </p>
            </div>
            <div class="panel-footer">
                <ul class="list-inline">
                    <li><?= Yii::t('podium/view', 'Threads') ?> <span class="badge">0</span></li>
                    <li><?= Yii::t('podium/view', 'Posts') ?> <span class="badge">0</span></li>
                </ul>
            </div>
        </div>
    </div>
</div><br>