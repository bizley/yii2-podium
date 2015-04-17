<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$this->registerJs('jQuery(\'[data-toggle="tooltip"]\').tooltip();', View::POS_READY, 'bootstrap-tooltip');

$btnClass = 'default';
$icon     = 'comment';
$iconDesc = 'No New Posts';

if ($model->locked) {
    $icon     = 'lock';
    $iconDesc = 'Locked Thread';
}
elseif ($model->pinned) {
    $icon     = 'pushpin';
    $iconDesc = 'Pinned Thread';
}
elseif ($model->posts >= 20) {
    $icon     = 'fire';
    $iconDesc = 'Hot Thread';
}

?><td>
    <a href="<?= Url::to(['thread', 'cid' => $model->category_id, 'fid' => $model->forum_id, 'id' => $model->id, 'slug' => $model->slug]) ?>" class="pull-left btn btn-<?= $btnClass ?>" style="margin-right:10px" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', $iconDesc) ?>">
        <span class="glyphicon glyphicon-<?= $icon ?>"></span>
    </a>
    <a href="<?= Url::to(['thread', 'cid' => $model->category_id, 'fid' => $model->forum_id, 'id' => $model->id, 'slug' => $model->slug]) ?>" class="center-block">
        <?= Html::encode($model->name) ?>
    </a>
</td>
<td class="text-right"><?= $model->posts > 0 ? $model->posts - 1 : 0 ?></td>
<td class="text-right"><?= $model->views ?></td>
<td><?= $model->getLatestPost() ?></td>