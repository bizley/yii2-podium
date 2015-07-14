<?php

use kartik\sortable\Sortable;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$this->title = Yii::t('podium/view', 'Contents');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Administration Dashboard'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

echo $this->render('/elements/admin/_navbar', ['active' => 'contents']);

?>

<br>

<div class="row">
    <div class="col-sm-3">
        <?= Html::beginTag('ul', ['class' => 'nav nav-pills nav-stacked']); ?>
        <?= Html::tag('li', Html::a(Html::tag('span', '', ['class' => 'glyphicon glyphicon-list']) . ' ' . Yii::t('podium/view', 'Categories List'), ['categories']), ['role' => 'presentation']); ?>
<?php foreach ($categories as $category): ?>
        <?= Html::tag('li', Html::a(Html::tag('span', '', ['class' => 'glyphicon glyphicon-chevron-' . ($category->id == $model->id ? 'down' : 'right')]) . ' ' . Html::encode($category->name), ['forums', 'cid' => $category->id]), ['role' => 'presentation', 'class' => $model->id == $category->id ? 'active' : null]); ?>
<?php if ($category->id == $model->id): ?>
<?php foreach ($forums as $forum): ?>
        <?= Html::tag('li', Html::a(Html::tag('span', '', ['class' => 'glyphicon glyphicon-bullhorn']) . ' ' . Html::encode($forum->name), ['forums', 'id' => $forum->id, 'cid' => $forum->category_id]), ['role' => 'presentation']); ?>
<?php endforeach; ?>        
        <?= Html::tag('li', Html::a(Html::tag('span', '', ['class' => 'glyphicon glyphicon-plus-sign']) . ' ' . Yii::t('podium/view', 'Create new forum'), ['new-forum', 'cid' => $category->id]), ['role' => 'presentation']); ?>
<?php endif; ?>
<?php endforeach; ?>
        <?= Html::tag('li', Html::a(Html::tag('span', '', ['class' => 'glyphicon glyphicon-plus']) . ' ' . Yii::t('podium/view', 'Create new category'), ['new-category']), ['role' => 'presentation']); ?>
        <?= Html::endTag('ul'); ?>
    </div>
    <div class="col-sm-9">
        
    </div>
</div><br>