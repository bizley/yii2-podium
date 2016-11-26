<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.5
 */

use bizley\podium\widgets\poll\Poll;
use yii\helpers\Html;

$this->title = Yii::t('podium/view', 'Delete Poll');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Main Forum'), 'url' => ['forum/index']];
$this->params['breadcrumbs'][] = ['label' => $model->thread->forum->category->name, 'url' => ['forum/category', 'id' => $model->thread->forum->category->id, 'slug' => $model->thread->forum->category->slug]];
$this->params['breadcrumbs'][] = ['label' => $model->thread->forum->name, 'url' => ['forum/forum', 'cid' => $model->thread->forum->category->id, 'id' => $model->thread->forum->id, 'slug' => $model->thread->forum->slug]];
$this->params['breadcrumbs'][] = ['label' => $model->thread->name, 'url' => ['forum/thread', 'cid' => $model->thread->forum->category->id, 'fid' => $model->thread->forum->id, 'id' => $model->thread->id, 'slug' => $model->thread->slug]];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="row">
    <div class="col-sm-8 col-sm-offset-2">
        <div class="panel panel-default">
            <?= Html::beginForm(); ?>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-12 text-center">
                            <?= Html::hiddenInput('poll', $model->id) ?>
                            <h3 class="text-danger"><?= Yii::t('podium/view', 'Are you sure you want to delete this poll?') ?></h3>
                            <p><?= Yii::t('podium/view', 'This action can not be undone.') ?></p>
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-sm-6">
                            <?= Html::submitButton('<span class="glyphicon glyphicon-ok-sign"></span> ' . Yii::t('podium/view', 'Delete Poll'), ['class' => 'btn btn-block btn-danger', 'name' => 'delete-button']) ?>
                        </div>
                        <div class="col-sm-6">
                            <?= Html::a('<span class="glyphicon glyphicon-remove"></span> ' . Yii::t('podium/view', 'Cancel'), ['forum/thread', 'cid' => $model->thread->forum->category->id, 'fid' => $model->thread->forum->id, 'id' => $model->thread->id, 'slug' => $model->thread->slug], ['class' => 'btn btn-block btn-default', 'name' => 'cancel-button']) ?>
                        </div>
                    </div>
                </div>
            <?= Html::endForm(); ?>
        </div>
    </div>
</div>

<?= Poll::widget(['model' => $model, 'display' => true]) ?>
<br>
