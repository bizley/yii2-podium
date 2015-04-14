<?php

use yii\helpers\Html;
use yii\helpers\Url;

?><td>
    <a href="<?= Url::to(['forum', 'cid' => $model->category_id, 'id' => $model->id, 'slug' => $model->slug]) ?>" class="center-block"><?= Html::encode($model->name) ?></a>
<?php if (!empty($model->sub)): ?>
    <small class="text-muted"><?= Html::encode($model->sub) ?></small>
<?php endif; ?>
</td>
<td class="text-right"><?= $model->getThreadsCount() ?></td>
<td class="text-right"><?= $model->getPostsCount() ?></td>
<td><?= $model->getLatestPost() ?></td>