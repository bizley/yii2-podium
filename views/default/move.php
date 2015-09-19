<?php

use yii\helpers\Html;

$this->title                   = Yii::t('podium/view', 'Move Thread');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Main Forum'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Html::encode($category->name), 'url' => ['category', 'id' => $category->id, 'slug' => $category->slug]];
$this->params['breadcrumbs'][] = ['label' => Html::encode($forum->name), 'url' => ['forum', 'cid' => $forum->category_id, 'id' => $forum->id, 'slug' => $forum->slug]];
$this->params['breadcrumbs'][] = ['label' => Html::encode($thread->name), 'url' => ['thread', 'cid' => $thread->category_id, 'fid' => $thread->forum_id, 'id' => $thread->id, 'slug' => $thread->slug]];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-sm-8 col-sm-offset-2">
        <div class="panel panel-default">
            <?= Html::beginForm(); ?>
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-12">
                        <?= Html::label(Yii::t('podium/view', 'Select a forum for this thread to be moved to'), 'forum') ?>
                        <p><?= Yii::t('podium/view', '* Forums you can moderate are marked with asterisk.') ?></p>
                        <?= Html::dropDownList('forum', null, $list, ['id' => 'forum', 'class' => 'form-control', 'options' => $options, 'encode' => false]) ?>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-12">
                        <?= Html::submitButton('<span class="glyphicon glyphicon-ok-sign"></span> ' . Yii::t('podium/view', 'Move Thread'), ['class' => 'btn btn-block btn-primary', 'name' => 'save-button']) ?>
                    </div>
                </div>
            </div>
            <?= Html::endForm(); ?>
        </div>
    </div>
</div><br>