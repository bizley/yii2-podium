<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title                   = Yii::t('podium/view', 'Threads started by {name}', ['name' => $user->getPodiumName()]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Members List'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-sm-12">
        <div class="panel-group" role="tablist">
            <?= $this->render('/elements/forum/_forum_section', ['model' => $model]) ?>
        </div>
    </div>
</div>