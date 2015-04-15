<?php

use yii\helpers\Html;
use yii\helpers\Url;

?><td>
    <a href="<?= Url::to(['thread', 'cid' => $model->category_id, 'fid' => $model->forum_id, 'id' => $model->id, 'slug' => $model->slug]) ?>" class="center-block">
        <span class="glyphicon glyphicon-comment" style="padding-right:10px"></span>
        <?= Html::encode($model->name) ?>
    </a>
</td>
<td class="text-right"><?= $model->posts - 1 ?></td>
<td class="text-right"><?= $model->views ?></td>
<td><?= $model->getLatestPost() ?></td>