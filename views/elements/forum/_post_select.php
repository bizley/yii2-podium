<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
use yii\helpers\Html;
use yii\web\View;

$this->registerJs('jQuery(\'[data-toggle="tooltip"]\').tooltip();', View::POS_READY, 'bootstrap-tooltip');

?><div class="row" id="post<?= $model->id ?>">
    <div class="col-sm-2" id="postAvatar<?= $model->id ?>">
        <?= Html::checkbox('post[]', false, ['value' => $model->id, 'label' => Yii::t('podium/view', 'Select this post')]) ?>
    </div>
    <div class="col-sm-10" id="postContent<?= $model->id ?>">
        <div class="popover right podium">
            <div class="arrow"></div>
            <div class="popover-title">
                <small class="pull-right">
                    <?= Html::tag('span', Yii::$app->formatter->asRelativeTime($model->created_at), ['data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::$app->formatter->asDatetime($model->created_at, 'long')]); ?>
<?php if ($model->edited && $model->edited_at): ?>
                    <em>(<?= Yii::t('podium/view', 'Edited') ?> <?= Html::tag('span', Yii::$app->formatter->asRelativeTime($model->edited_at), ['data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::$app->formatter->asDatetime($model->edited_at, 'long')]); ?>)</em>
<?php endif; ?>
                </small>
                <?= $model->podiumUser->user->getPodiumTag() ?>
                <small>
                    <span class="label label-info" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Number of posts') ?>"><?= $model->podiumUser->getPostsCount() ?></span>
                </small>
            </div>
            <div class="popover-content podium-content">
                <?= $model->content ?>
            </div>
        </div>
    </div>
</div>
