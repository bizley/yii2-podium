<?php

use bizley\podium\widgets\Avatar;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$this->registerJs('jQuery(\'[data-toggle="tooltip"]\').tooltip();', View::POS_READY, 'bootstrap-tooltip');
$this->registerJs('jQuery(\'.podium-quote\').click(function(e){
    e.preventDefault();
    var selection = \'\';
    if (window.getSelection) selection = window.getSelection().toString();
    else if (document.selection && document.selection.type != \'Control\') selection = document.selection.createRange().text;
    jQuery(this).parent().find(\'.quote-selection\').val(selection);
    jQuery(this).parent().find(\'.quick-quote-form\').submit();
})', View::POS_READY, 'podium-quote');

$model->markSeen();

?><div class="row" id="post<?= $model->id ?>">
    <div class="col-sm-2 text-center" id="postAvatar<?= $model->id ?>">
        <?= Avatar::widget(['author' => $model->user, 'showName' => false]) ?>
    </div>
    <div class="col-sm-10" id="postContent<?= $model->id ?>">
        <div class="popover right podium">
            <div class="arrow"></div>
            <div class="popover-title">
                <small class="pull-right"><?= Html::tag('span', Yii::$app->formatter->asRelativeTime($model->created_at), ['data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::$app->formatter->asDatetime($model->created_at, 'long')]); ?></small>
                <?= $model->user->getPodiumTag() ?>
            </div>
            <div class="popover-content podium-content">
                <?= $model->content ?>
                <div class="podium-action-bar">
<?= Html::beginForm(['post', 'cid' => $category, 'fid' => $model->forum_id, 'tid' => $model->thread_id, 'pid' => $model->id], 'post', ['class' => 'quick-quote-form']); ?>
                    <?= Html::hiddenInput('quote', '', ['class' => 'quote-selection']); ?>
<?= Html::endForm(); ?>
                    <button class="btn btn-primary btn-xs podium-quote" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Reply with quote') ?>"><span class="glyphicon glyphicon-leaf"></span></button>
                    <a href="<?= Url::to(['show', 'id' => $model->id]) ?>" class="btn btn-default btn-xs" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Link to this post') ?>"><span class="glyphicon glyphicon-link"></span></a>
                    <a href="" class="btn btn-success btn-xs" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Thumb up') ?>"><span class="glyphicon glyphicon-thumbs-up"></span></a>
                    <a href="" class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Thumb down') ?>"><span class="glyphicon glyphicon-thumbs-down"></span></a>
                    <a href="" class="btn btn-warning btn-xs" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Report this post') ?>"><span class="glyphicon glyphicon-flag"></span></a>
                </div>
            </div>
        </div>
    </div>
</div>
