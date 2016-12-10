<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

use bizley\podium\Podium;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;

$this->registerJs("$('[data-toggle=\"popover\"]').popover();");
$firstToSee = $model->firstToSee();
?>
<td class="podium-thread-line">
    <a href="<?= Url::to(['forum/show', 'id' => $firstToSee->id]) ?>" class="podium-go-to-new pull-right" style="margin-right:10px" data-pjax="0" data-toggle="popover" data-container="body" data-placement="left" data-trigger="hover focus" data-html="true" data-content="<small><?= str_replace('"', '&quote;', StringHelper::truncateWords($firstToSee->parsedContent, 20, '...', true)) ?><br><strong><?= $firstToSee->author->podiumName ?></strong> <?= Podium::getInstance()->formatter->asRelativeTime($firstToSee->updated_at) ?></small>" title="<?= Yii::t('podium/view', 'First New Post') ?>">
        <span class="glyphicon glyphicon-leaf"></span>
    </a>
    <a href="<?= Url::to(['forum/thread', 'cid' => $model->category_id, 'fid' => $model->forum_id, 'id' => $model->id, 'slug' => $model->slug]) ?>" class="hidden-xs pull-left btn btn-<?= $model->getCssClass() ?>" style="margin-right:10px" data-pjax="0" data-toggle="tooltip" data-placement="top" title="<?= $model->getDescription() ?>">
        <span class="glyphicon glyphicon-<?= $model->getIcon() ?>"></span>
    </a>
    <a href="<?= Url::to(['forum/thread', 'cid' => $model->category_id, 'fid' => $model->forum_id, 'id' => $model->id, 'slug' => $model->slug]) ?>" class="hidden-lg hidden-md hidden-sm pull-left btn btn-<?= $model->getCssClass() ?> btn-xs" style="margin-right:5px" data-pjax="0" data-toggle="tooltip" data-placement="top" title="<?= $model->getDescription() ?>">
        <span class="glyphicon glyphicon-<?= $model->getIcon() ?>"></span>
    </a>
    <a href="<?= Url::to(['forum/thread', 'cid' => $model->category_id, 'fid' => $model->forum_id, 'id' => $model->id, 'slug' => $model->slug]) ?>" class="center-block <?= $model->locked ? 'text-danger' : '' ?>" data-pjax="0">
        <?= $model->pinned ? '<mark>' : '' ?><?= Html::encode($model->name) ?><?= $model->pinned ? '</mark>' : '' ?>
    </a>
</td>
<td class="text-center"><?= $model->posts > 0 ? $model->posts - 1 : 0 ?></td>
<td class="text-center"><?= $model->views ?></td>
<td>
<?php if (!empty($model->latest) && !empty($model->latest->author)): ?>
    <small>
        <?= $model->latest->author->podiumTag ?>
        <span class="clearfix hidden-xs"><?= Podium::getInstance()->formatter->asDatetime($model->latest->created_at, 'medium') ?></span>
        <span class="clearfix hidden-sm hidden-md hidden-lg"><?= Podium::getInstance()->formatter->asDatetime($model->latest->created_at, 'short') ?></span>
    </small>
<?php endif; ?>
</td>
