<?php

use yii\helpers\Html;
use yii\helpers\Url;
//use yii\web\View;
use yii\bootstrap\ActiveForm;
use bizley\podium\components\Helper;

$this->title                   = Yii::t('podium/view', 'My Profile');
$this->params['breadcrumbs'][] = $this->title;

//$this->registerJs('$(\'[data-toggle="tooltip"]\').tooltip()', View::POS_READY, 'bootstrap-tooltip');
?>
<div class="row">
    <div class="col-sm-3">
        <?= $this->render('/elements/profile/_navbar', ['active' => 'profile']) ?>
    </div>
    <div class="col-sm-9">
        <div class="panel panel-default">
            <div class="panel-body">
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