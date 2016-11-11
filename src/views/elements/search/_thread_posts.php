<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

use bizley\podium\Module as Podium;
use yii\helpers\Html;
use yii\helpers\Url;

$this->registerJs("$('[data-toggle=\"popover\"]').popover();");

?>
<td class="podium-thread-line">
    <a href="<?= Url::to(['forum/show', 'id' => $model->post->id]) ?>" class="podium-go-to-new pull-right" style="margin-right:10px" data-toggle="popover" data-container="body" data-placement="left" data-trigger="hover focus" data-html="true" data-content="<small><?= Html::encode(strip_tags($model->post->content)) ?><br><strong><?= $model->post->author->podiumName ?></strong> <?= Podium::getInstance()->formatter->asRelativeTime($model->post->updated_at) ?></small>" title="<?= Yii::t('podium/view', 'Found Post') ?>">
        <span class="glyphicon glyphicon-comment"></span>
    </a>
    <a href="<?= Url::to(['forum/show', 'id' => $model->post->id]) ?>" class="pull-left btn btn-<?= $model->thread->getCssClass() ?>" style="margin-right:10px" data-toggle="tooltip" data-placement="top" title="<?= $model->thread->getDescription() ?>">
        <span class="glyphicon glyphicon-<?= $model->thread->getIcon() ?>"></span>
    </a>
    <a href="<?= Url::to(['forum/show', 'id' => $model->post->id]) ?>" class="center-block">
        <?= Html::encode($model->thread->name) ?>
    </a>
</td>
<td class="text-center"><?= $model->thread->posts > 0 ? $model->thread->posts - 1 : 0 ?></td>
<td class="text-center"><?= $model->thread->views ?></td>
<td>
    <small><?= $model->post->author->podiumTag ?><br><?= Podium::getInstance()->formatter->asDatetime($model->post->created_at) ?></small>
</td>
