<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;

$this->registerJs("$('[data-toggle=\"popover\"]').popover();");
$firstToSee = $model->firstToSee();
?>
<td class="podium-thread-line">
    <a href="<?= Url::to(['default/show', 'id' => $firstToSee->id]) ?>" class="podium-go-to-new pull-right" style="margin-right:10px" data-pjax="0" data-toggle="popover" data-container="body" data-placement="left" data-trigger="hover focus" data-html="true" data-content="<small><?= str_replace('"', '&quote;', StringHelper::truncateWords($firstToSee->content, 20, '...', true)) ?><br><strong><?= $firstToSee->author->podiumName ?></strong> <?= Yii::$app->formatter->asRelativeTime($firstToSee->updated_at) ?></small>" title="<?= Yii::t('podium/view', 'First New Post') ?>">
        <span class="glyphicon glyphicon-leaf"></span>
    </a>
    <a href="<?= Url::to(['default/thread', 'cid' => $model->category_id, 'fid' => $model->forum_id, 'id' => $model->id, 'slug' => $model->slug]) ?>" class="pull-left btn btn-<?= $model->getCssClass() ?>" style="margin-right:10px" data-pjax="0" data-toggle="tooltip" data-placement="top" title="<?= $model->getDescription() ?>">
        <span class="glyphicon glyphicon-<?= $model->getIcon() ?>"></span>
    </a>
    <a href="<?= Url::to(['default/thread', 'cid' => $model->category_id, 'fid' => $model->forum_id, 'id' => $model->id, 'slug' => $model->slug]) ?>" class="center-block" data-pjax="0">
        <?= Html::encode($model->name) ?>
    </a>
</td>
<td class="text-center"><?= $model->posts > 0 ? $model->posts - 1 : 0 ?></td>
<td class="text-center"><?= $model->views ?></td>
<td>
<?php if (!empty($model->latest) && !empty($model->latest->author)): ?>
    <small><?= $model->latest->author->podiumTag ?><br><?= Yii::$app->formatter->asDatetime($model->latest->created_at) ?></small>
<?php endif; ?>
</td>
