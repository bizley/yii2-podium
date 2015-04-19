<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$this->registerJs('jQuery(\'[data-toggle="tooltip"]\').tooltip();', View::POS_READY, 'bootstrap-tooltip');

?><td class="podium-thread-line">
    <a href="<?= Url::to(['thread', 'cid' => $model->category_id, 'fid' => $model->forum_id, 'id' => $model->id, 'slug' => $model->slug]) ?>" class="podium-go-to-new pull-right" style="margin-right:10px" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Go to the new post') ?>">
        <span class="glyphicon glyphicon-leaf"></span>
    </a>
    <a href="<?= Url::to(['thread', 'cid' => $model->category_id, 'fid' => $model->forum_id, 'id' => $model->id, 'slug' => $model->slug]) ?>" class="pull-left btn btn-<?= $model->getClass() ?>" style="margin-right:10px" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', $model->getDescription()) ?>">
        <span class="glyphicon glyphicon-<?= $model->getIcon() ?>"></span>
    </a>
    <a href="<?= Url::to(['thread', 'cid' => $model->category_id, 'fid' => $model->forum_id, 'id' => $model->id, 'slug' => $model->slug]) ?>" class="center-block">
        <?= Html::encode($model->name) ?>
    </a>
</td>
<td class="text-center"><?= $model->posts > 0 ? $model->posts - 1 : 0 ?></td>
<td class="text-center"><?= $model->views ?></td>
<td>
    <small><?= $model->latest->user->getPodiumTag() ?><br><?= Yii::$app->formatter->asDatetime($model->latest->created_at) ?></small>
</td>