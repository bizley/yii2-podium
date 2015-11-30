<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */

use yii\helpers\Html;
use bizley\podium\components\Helper;
use cebe\gravatar\Gravatar;

$this->title = Yii::t('podium/view', 'My Profile');
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
                    'email'        => $model->email,
                    'defaultImage' => 'identicon',
                    'rating'       => 'r',
                    'options'      => [
                        'alt'   => Html::encode($model->podiumName),
                        'class' => 'podium-avatar img-circle img-responsive pull-right',
                ]]); ?>
<?php elseif (!empty($model->meta->avatar)): ?>
                <img class="podium-avatar img-circle img-responsive pull-right" src="/avatars/<?= $model->meta->avatar ?>" alt="<?= Html::encode($model->podiumName) ?>">
<?php else: ?>
                <img class="podium-avatar img-circle img-responsive pull-right" src="<?= Helper::defaultAvatar() ?>" alt="<?= Html::encode($model->podiumName) ?>">
<?php endif; ?>
                <h2>
                    <?= Html::encode($model->podiumName) ?> 
                    <small>
                        <?= Html::encode($model->email) ?> 
                        <?= Helper::roleLabel($model->role) ?>
                    </small>
                </h2>
                <p><?= Yii::t('podium/view', 'Member since {date}', ['date' => Yii::$app->formatter->asDatetime($model->created_at, 'long')]) ?> (<?= Yii::$app->formatter->asRelativeTime($model->created_at) ?>)</p>
                <p>
                    <a href="" class="btn btn-default"><span class="glyphicon glyphicon-search"></span> <?= Yii::t('podium/view', 'Show all threads started by me') ?></a> 
                    <a href="" class="btn btn-default"><span class="glyphicon glyphicon-search"></span> <?= Yii::t('podium/view', 'Show all posts created by me') ?></a>
                </p>
            </div>
            <div class="panel-footer">
                <ul class="list-inline">
                    <li><?= Yii::t('podium/view', 'Threads') ?> <span class="badge"><?= $model->threadsCount ?></span></li>
                    <li><?= Yii::t('podium/view', 'Posts') ?> <span class="badge"><?= $model->postsCount ?></span></li>
                </ul>
            </div>
        </div>
    </div>
</div><br>
