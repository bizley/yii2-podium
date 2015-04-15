<?php

use bizley\podium\widgets\Avatar;
use yii\helpers\Html;

?><div class="row">
    <div class="col-sm-2 text-center">
        <?= Avatar::widget(['author' => $model->user, 'showName' => false]) ?>
    </div>
    <div class="col-sm-10">
        <div class="popover right podium">
            <div class="arrow"></div>
            <div class="popover-title">
                <small class="pull-right"><?= Html::tag('span', Yii::$app->formatter->asRelativeTime($model->created_at), ['data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::$app->formatter->asDatetime($model->created_at, 'long')]); ?></small>
                <?= $model->user->getPodiumTag() ?>
            </div>
            <div class="popover-content">
                <?= $model->content ?>
            </div>
        </div>
    </div>
</div>
