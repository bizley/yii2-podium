<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ListView;

$this->title                   = Html::encode($thread->name);
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Main Forum'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Html::encode($category->name), 'url' => ['category', 'id' => $category->id, 'slug' => $category->slug]];
$this->params['breadcrumbs'][] = ['label' => Html::encode($forum->name), 'url' => ['forum', 'cid' => $forum->category_id, 'id' => $forum->id, 'slug' => $forum->slug]];
$this->params['breadcrumbs'][] = $this->title;

echo ListView::widget([
    'dataProvider' => $dataProvider,
    'itemView' => '/elements/forum/_post',
    'summary' => '',
    'emptyText' => Yii::t('podium/view', 'No posts have been added yet.'),
    'emptyTextOptions' => ['tag' => 'h3', 'class' => 'text-muted'],
    'pager' => ['options' => ['class' => 'pagination pull-right']]
]); ?>

<br>
<div class="row">
    <div class="col-sm-12 text-right">
        <a href="<?= Url::to(['post', 'cid' => $category->id, 'fid' => $forum->id, 'tid' => $thread->id]) ?>" class="btn btn-primary"><span class="glyphicon glyphicon-leaf"></span> New Reply</a>
    </div>
</div>
<br>