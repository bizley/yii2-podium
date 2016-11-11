<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

use bizley\podium\Podium;
use yii\helpers\Html;
use yii\helpers\Url;

?>
<td>
    <a href="<?= Url::to(['forum/forum', 'cid' => $model->category_id, 'id' => $model->id, 'slug' => $model->slug]) ?>" class="center-block"><?= Html::encode($model->name) ?></a>
<?php if (!empty($model->sub)): ?>
    <small class="text-muted"><?= Html::encode($model->sub) ?></small>
<?php endif; ?>
</td>
<td class="text-right"><?= $model->threads ?></td>
<td class="text-right"><?= $model->posts ?></td>
<td>
<?php if (!empty($model->latest) && !empty($model->latest->thread)): ?>
    <a href="<?= Url::to(['forum/show', 'id' => $model->latest->id]) ?>" class="center-block"><?= Html::encode($model->latest->thread->name) ?></a>
    <small>
        <?= $model->latest->author->podiumTag ?>
        <span class="hidden-xs"><?= Podium::getInstance()->formatter->asDatetime($model->latest->created_at, 'medium') ?></span>
        <span class="hidden-sm hidden-md hidden-lg"><?= Podium::getInstance()->formatter->asDatetime($model->latest->created_at, 'short') ?></span>
    </small>
<?php endif; ?>
</td>
