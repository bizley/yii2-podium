<?php

use yii\helpers\Html;
use yii\helpers\Url;

?><td></td>
<td>
    <a href="<?= Url::to(['thread', 'cid' => $model->category_id, 'fid' => $model->forum_id, 'id' => $model->id, 'slug' => $model->slug]) ?>" class="center-block"><?= Html::encode($model->name) ?></a>
</td>
<td class="text-right"><?= $model->getRepliesCount() ?></td>
<td class="text-right"><?= $model->getViewsCount() ?></td>
<td><?= $model->getLatestPost() ?></td>