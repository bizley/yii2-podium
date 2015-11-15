<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$this->registerJs('jQuery(\'[data-toggle="popover"]\').popover();', View::POS_READY, 'bootstrap-popover');

$firstToSee = $model->firstToSee();

?><td class="podium-thread-line">
    <a href="<?= Url::to(['default/show', 'id' => $firstToSee->id]) ?>" class="podium-go-to-new pull-right" style="margin-right:10px" data-toggle="popover" data-container="body" data-placement="left" data-trigger="hover focus" data-html="true" data-content="<small><?= Html::encode(strip_tags($firstToSee->content)) ?><br><strong><?= $firstToSee->podiumUser->user->getPodiumName() ?></strong> <?= Yii::$app->formatter->asRelativeTime($firstToSee->updated_at) ?></small>" title="<?= Yii::t('podium/view', 'First New Post') ?>">
        <span class="glyphicon glyphicon-leaf"></span>
    </a>
    <a href="<?= Url::to(['default/thread', 'cid' => $model->category_id, 'fid' => $model->forum_id, 'id' => $model->id, 'slug' => $model->slug]) ?>" class="pull-left btn btn-<?= $model->getClass() ?>" style="margin-right:10px" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', $model->getDescription()) ?>">
        <span class="glyphicon glyphicon-<?= $model->getIcon() ?>"></span>
    </a>
    <a href="<?= Url::to(['default/thread', 'cid' => $model->category_id, 'fid' => $model->forum_id, 'id' => $model->id, 'slug' => $model->slug]) ?>" class="center-block">
        <?= Html::encode($model->name) ?>
    </a>
</td>
<td class="text-center"><?= $model->posts > 0 ? $model->posts - 1 : 0 ?></td>
<td class="text-center"><?= $model->views ?></td>
<td>
<?php if (!empty($model->latest) && !empty($model->latest->podiumUser)): ?>
    <small><?= $model->latest->podiumUser->user->getPodiumTag() ?><br><?= Yii::$app->formatter->asDatetime($model->latest->created_at) ?></small>
<?php endif; ?>
</td>