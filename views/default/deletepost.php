<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */

use yii\helpers\Html;

$this->title = Yii::t('podium/view', 'Delete Post');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Main Forum'), 'url' => ['default/index']];
$this->params['breadcrumbs'][] = ['label' => $category->name, 'url' => ['default/category', 'id' => $category->id, 'slug' => $category->slug]];
$this->params['breadcrumbs'][] = ['label' => $forum->name, 'url' => ['default/forum', 'cid' => $forum->category_id, 'id' => $forum->id, 'slug' => $forum->slug]];
$this->params['breadcrumbs'][] = ['label' => $thread->name, 'url' => ['default/thread', 'cid' => $thread->category_id, 'fid' => $thread->forum_id, 'id' => $thread->id, 'slug' => $thread->slug]];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="row">
    <div class="col-sm-8 col-sm-offset-2">
        <div class="panel panel-default">
            <?= Html::beginForm(); ?>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-12 text-center">
                            <?= Html::hiddenInput('post', $model->id) ?>
                            <h3 class="text-danger"><?= Yii::t('podium/view', 'Are you sure you want to delete this post?') ?></h3>
                            <p><?= Yii::t('podium/view', 'This action can not be undone.') ?></p>
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-sm-6">
                            <?= Html::submitButton('<span class="glyphicon glyphicon-ok-sign"></span> ' . Yii::t('podium/view', 'Delete Post'), ['class' => 'btn btn-block btn-danger', 'name' => 'delete-button']) ?>
                        </div>
                        <div class="col-sm-6">
                            <?= Html::a('<span class="glyphicon glyphicon-remove"></span> ' . Yii::t('podium/view', 'Cancel'), ['default/thread', 'cid' => $thread->category_id, 'fid' => $thread->forum_id, 'id' => $thread->id, 'slug' => $thread->slug], ['class' => 'btn btn-block btn-default', 'name' => 'cancel-button']) ?>
                        </div>
                    </div>
                </div>
            <?= Html::endForm(); ?>
        </div>
    </div>
</div><br>
<?= $this->render('/elements/forum/_post', ['model' => $model, 'category' => $category->id, 'slug' => $thread->slug]) ?>
<br>
