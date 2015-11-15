<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
use bizley\podium\widgets\Avatar;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$this->registerJs('jQuery(\'[data-toggle="tooltip"]\').tooltip();', View::POS_READY, 'bootstrap-tooltip');

$content = $model->postData->content;
$thread = Html::encode($model->postData->thread->name);
if ($type == 'topics') {
    foreach ($words as $word) {
        $thread = preg_replace("/$word/", '*#*&*?*' . $word . '*%*(*!*', $thread);
    }
    $thread = str_replace(['*#*&*?*', '*%*(*!*'], ['<mark>', '</mark>'], $thread);
}
else {
    foreach ($words as $word) {
        $content = preg_replace("/$word/", '*#*&*?*' . $word . '*%*(*!*', $content);
    }
    $content = str_replace(['*#*&*?*', '*%*(*!*'], ['<mark>', '</mark>'], $content);
}

?><div class="row" id="post<?= $model->postData->id ?>">
    <div class="col-sm-2 text-center" id="postAvatar<?= $model->postData->id ?>">
        <?= Avatar::widget(['author' => $model->postData->podiumUser->user, 'showName' => false]) ?>
    </div>
    <div class="col-sm-10" id="postContent<?= $model->postData->id ?>">
        <div class="popover right podium">
            <div class="arrow"></div>
            <div class="popover-title">
                <small class="pull-right">
                    <?= Html::tag('span', Yii::$app->formatter->asRelativeTime($model->postData->created_at), ['data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::$app->formatter->asDatetime($model->postData->created_at, 'long')]); ?>
<?php if ($model->postData->edited && $model->postData->edited_at): ?>
                    <em>(<?= Yii::t('podium/view', 'Edited') ?> <?= Html::tag('span', Yii::$app->formatter->asRelativeTime($model->postData->edited_at), ['data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::$app->formatter->asDatetime($model->postData->edited_at, 'long')]); ?>)</em>
<?php endif; ?>
                </small>
                <?= $model->postData->podiumUser->user->getPodiumTag() ?>
                <small>
                    <span class="label label-info" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Number of posts') ?>"><?= $model->postData->podiumUser->getPostsCount() ?></span>
                </small>
            </div>
            <div class="popover-content podium-content">
                <a href="<?= Url::to(['default/thread', 'cid' => $model->postData->thread->category_id, 'fid' => $model->postData->forum_id, 'id' => $model->postData->thread_id, 'slug' => $model->postData->thread->slug]) ?>"><span class="glyphicon glyphicon-comment"></span> <?= $thread ?></a><br><br>
                <?= $content ?>
                <div class="podium-action-bar">
                    <a href="<?= Url::to(['default/show', 'id' => $model->postData->id]) ?>" class="btn btn-default btn-xs" data-pjax="0" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Direct link to this post') ?>"><span class="glyphicon glyphicon-link"></span></a>
                </div>
            </div>
        </div>
    </div>
</div>