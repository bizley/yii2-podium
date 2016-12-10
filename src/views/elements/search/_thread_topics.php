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

$this->registerJs("$('[data-toggle=\"popover\"]').popover();");

$firstToSee = $model->firstToSee();

?>
<td class="podium-thread-line">
    <a href="<?= Url::to(['forum/show', 'id' => $firstToSee->id]) ?>" class="podium-go-to-new pull-right" style="margin-right:10px" data-toggle="popover" data-container="body" data-placement="left" data-trigger="hover focus" data-html="true" data-content="<small><?= Html::encode(strip_tags($firstToSee->parsedContent)) ?><br><strong><?= $firstToSee->author->podiumName ?></strong> <?= Podium::getInstance()->formatter->asRelativeTime($firstToSee->updated_at) ?></small>" title="<?= Yii::t('podium/view', 'First New Post') ?>">
        <span class="glyphicon glyphicon-leaf"></span>
    </a>
    <a href="<?= Url::to(['forum/thread', 'cid' => $model->category_id, 'fid' => $model->forum_id, 'id' => $model->id, 'slug' => $model->slug]) ?>" class="pull-left btn btn-<?= $model->getCssClass() ?>" style="margin-right:10px" data-toggle="tooltip" data-placement="top" title="<?= $model->getDescription() ?>">
        <span class="glyphicon glyphicon-<?= $model->getIcon() ?>"></span>
    </a>
    <a href="<?= Url::to(['forum/thread', 'cid' => $model->category_id, 'fid' => $model->forum_id, 'id' => $model->id, 'slug' => $model->slug]) ?>" class="center-block">
        <?= Html::encode($model->name) ?>
    </a>
</td>
<td class="text-center"><?= $model->posts > 0 ? $model->posts - 1 : 0 ?></td>
<td class="text-center"><?= $model->views ?></td>
<td>
<?php if (!empty($model->latest) && !empty($model->latest->author)): ?>
    <small><?= $model->latest->author->podiumTag ?><br><?= Podium::getInstance()->formatter->asDatetime($model->latest->created_at) ?></small>
<?php endif; ?>
</td>
