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

$postModel = !empty($model->posts[0]) ? $model->posts[0] : $model->postData;
?>
<td class="podium-thread-line">
    <a href="<?= Url::to(['forum/show', 'id' => $postModel->id]) ?>" class="podium-go-to-new pull-right" style="margin-right:10px" data-toggle="popover" data-container="body" data-placement="left" data-trigger="hover focus" data-html="true" data-content="<small><?= Html::encode(strip_tags($postModel->parsedContent)) ?><br><strong><?= $postModel->author->podiumName ?></strong> <?= Podium::getInstance()->formatter->asRelativeTime($postModel->updated_at) ?></small>" title="<?= Yii::t('podium/view', 'Found Post') ?>">
        <span class="glyphicon glyphicon-comment"></span>
    </a>
    <a href="<?= Url::to(['forum/show', 'id' => $postModel->id]) ?>" class="pull-left btn btn-<?= $postModel->thread->getCssClass() ?>" style="margin-right:10px" data-toggle="tooltip" data-placement="top" title="<?= $postModel->thread->getDescription() ?>">
        <span class="glyphicon glyphicon-<?= $postModel->thread->getIcon() ?>"></span>
    </a>
    <a href="<?= Url::to(['forum/show', 'id' => $postModel->id]) ?>" class="center-block">
        <?= Html::encode($postModel->thread->name) ?>
    </a>
</td>
<td class="text-center">
    <?= $postModel->thread->posts > 0 ? $postModel->thread->posts - 1 : 0 ?>
</td>
<td class="text-center">
    <?= $postModel->thread->views ?>
</td>
<td>
    <small><?= $postModel->author->podiumTag ?><br><?= Podium::getInstance()->formatter->asDatetime($postModel->created_at) ?></small>
</td>
