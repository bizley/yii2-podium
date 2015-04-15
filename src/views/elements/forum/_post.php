<?php

use bizley\podium\widgets\Avatar;
use yii\helpers\Html;
use yii\web\View;

$this->registerJs('jQuery(\'[data-toggle="tooltip"]\').tooltip();', View::POS_READY, 'bootstrap-tooltip');

?><div class="row" id="post<?= $model->id ?>">
    <div class="col-sm-2 text-center" id="postAvatar<?= $model->id ?>">
        <?= Avatar::widget(['author' => $model->user, 'showName' => false]) ?>
    </div>
    <div class="col-sm-10" id="postContent<?= $model->id ?>">
        <div class="popover right podium">
            <div class="arrow"></div>
            <div class="popover-title">
                <small class="pull-right"><?= Html::tag('span', Yii::$app->formatter->asRelativeTime($model->created_at), ['data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::$app->formatter->asDatetime($model->created_at, 'long')]); ?></small>
                <?= $model->user->getPodiumTag() ?>
            </div>
            <div class="popover-content podium-content">
                <?= $model->content ?>
            </div>
        </div>
    </div>
</div>
