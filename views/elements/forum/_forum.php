<?php

use yii\helpers\Html;
use yii\helpers\Url;

?><td>
    <a href="<?= Url::to(['forum', 'cid' => $model->category_id, 'id' => $model->id, 'slug' => $model->slug]) ?>" class="center-block"><?= Html::encode($model->name) ?></a>
<?php if (!empty($model->sub)): ?>
    <small class="text-muted"><?= Html::encode($model->sub) ?></small>
<?php endif; ?>
</td>
<td class="text-right"><?= $model->threads ?></td>
<td class="text-right"><?= $model->posts ?></td>
<td>
<?php if (!empty($model->latest) && !empty($model->latest->thread)): ?>
    <a href="<?= Url::to(['show', 'id' => $model->latest->id]) ?>" class="center-block"><?= Html::encode($model->latest->thread->name) ?></a>
    <small><?= $model->latest->user->user->getPodiumTag() ?> <?= Yii::$app->formatter->asDatetime($model->latest->created_at) ?></small>
<?php endif; ?>
</td>