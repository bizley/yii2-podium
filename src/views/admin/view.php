<?php

use yii\helpers\Html;
use yii\web\View;
use bizley\podium\models\User;
use bizley\podium\components\Helper;

$this->title                   = Yii::t('podium/view', 'Member View');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Administration Dashboard'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Forum Members'), 'url' => ['members']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs('$(\'[data-toggle="tooltip"]\').tooltip()', View::POS_READY, 'bootstrap-tooltip');

echo $this->render('/elements/admin/_navbar', ['active' => 'members']);
?>

<br>
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="pull-right">
<?php if ($model->id !== Yii::$app->user->id): ?>
                    <?= Html::a('<span class="glyphicon glyphicon-envelope"></span>', '', ['class' => 'btn btn-default btn-lg', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'Send Message')]); ?>
<?php else: ?>
                    <?= Html::a('<span class="glyphicon glyphicon-envelope"></span>', '', ['class' => 'btn btn-lg disabled text-muted']); ?>
<?php endif; ?>
<?php if ($model->id !== Yii::$app->user->id): ?>
<?php if ($model->status !== User::STATUS_BANNED): ?>
                    <?= Html::a('<span class="glyphicon glyphicon-ban-circle"></span>', '', ['class' => 'btn btn-danger btn-lg', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'Ban Member')]); ?>
<?php else: ?>
                    <?= Html::a('<span class="glyphicon glyphicon-ok-circle"></span>', '', ['class' => 'btn btn-success btn-lg', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'Unban Member')]); ?>
<?php endif; ?>
<?php else: ?>
                    <?= Html::a('<span class="glyphicon glyphicon-ban-circle"></span>', '', ['class' => 'btn btn-lg disabled text-muted']); ?>
<?php endif; ?>
<?php if ($model->id !== Yii::$app->user->id): ?>
                    <?= Html::a('<span class="glyphicon glyphicon-trash"></span>', '', ['class' => 'btn btn-danger btn-lg', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'Delete Member')]); ?>
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
                <p><?= Yii::t('podium/view', 'Member since {DATE}', ['DATE' => Yii::$app->formatter->asDatetime($model->created_at, 'long')]) ?> (<?= Yii::$app->formatter->asRelativeTime($model->created_at) ?>)</p>
<?php if ($model->status == User::STATUS_REGISTERED): ?>
                <p><em><?= Yii::t('podium/view', 'The account is awaiting activation.') ?></em></p>
<?php else: ?>
                <p>
                    <a href="" class="btn btn-default"><span class="glyphicon glyphicon-search"></span> <?= Yii::t('podium/view', 'Find all threads started by {NAME}', ['NAME' => Html::encode($model->getPodiumName())]) ?></a> 
                    <a href="" class="btn btn-default"><span class="glyphicon glyphicon-search"></span> <?= Yii::t('podium/view', 'Find all posts created by {NAME}', ['NAME' => Html::encode($model->getPodiumName())]) ?></a>
                </p>
<?php endif; ?>
            </div>
            <div class="panel-footer">
                <ul class="list-inline">
                    <li><?= Yii::t('podium/view', 'Threads') ?> <span class="badge">0</span></li>
                    <li><?= Yii::t('podium/view', 'Posts') ?> <span class="badge">0</span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
