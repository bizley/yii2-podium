<?php

use kartik\sortable\Sortable;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$this->title = Yii::t('podium/view', 'Moderators');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Administration Dashboard'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

echo $this->render('/elements/admin/_navbar', ['active' => 'mods']);

function prepareContent($category) {
    $actions = [];
    $actions[] = Html::button(Html::tag('span', '', ['class' => 'glyphicon glyphicon-eye-' . ($category->visible ? 'open' : 'close')]), ['class' => 'btn btn-xs text-muted', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', $category->visible ? 'Category visible for guests' : 'Category hidden for guests')]);
    $actions[] = Html::a(Html::tag('span', '', ['class' => 'glyphicon glyphicon-list']), ['forums', 'cid' => $category->id], ['class' => 'btn btn-default btn-xs', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'List Forums')]);
    $actions[] = Html::a(Html::tag('span', '', ['class' => 'glyphicon glyphicon-plus-sign']), ['new-forum', 'cid' => $category->id], ['class' => 'btn btn-success btn-xs', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'Create new forum in this category')]);
    $actions[] = Html::a(Html::tag('span', '', ['class' => 'glyphicon glyphicon-cog']), ['edit-category', 'id' => $category->id], ['class' => 'btn btn-default btn-xs', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'Edit Category')]);
    $actions[] = Html::tag('span', Html::button(Html::tag('span', '', ['class' => 'glyphicon glyphicon-trash']), ['class' => 'btn btn-danger btn-xs', 'data-url' => Url::to(['delete-category', 'id' => $category->id]), 'data-toggle' => 'modal', 'data-target' => '#podiumModalDelete']), ['data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'Delete Category')]);

    return Html::tag('p', implode(' ', $actions), ['class' => 'pull-right']) . Html::tag('span', Html::encode($category->name), ['class' => 'podium-forum', 'data-id' => $category->id]);
}

$items = [];
//foreach ($dataProvider as $category) {
//    $items[] = [
//        'content' => prepareContent($category),
//    ];
//}

if (!empty($items)) {
    $this->registerJs('jQuery(\'[data-toggle="tooltip"]\').tooltip()', View::POS_READY, 'bootstrap-tooltip');
}

?>

<br>

<div class="row">
    <br>
    <div class="col-sm-3">
        <?= Html::beginTag('ul', ['class' => 'nav nav-pills nav-stacked']); ?>
<?php foreach ($moderators as $moderator): ?>
        <?= Html::tag('li', Html::a(Html::tag('span', '', ['class' => 'glyphicon glyphicon-chevron-right']) . ' ' . Html::encode($moderator->getPodiumName()), ['mods', 'id' => $moderator->id]), ['role' => 'presentation', 'class' => $moderator->id == $mod->id ? 'active' : null]); ?>
<?php endforeach; ?>
        <?= Html::endTag('ul'); ?>
    </div>
    <div class="col-sm-4">
<?php /*if (empty($items)): ?>
        <h3><?= Yii::t('podium/view', 'No categories have been added yet.') ?></h3>
<?php else: ?>
        <?= Sortable::widget([
            'showHandle' => true,
            'handleLabel' => '<span class="btn btn-default btn-xs pull-left" style="margin-right:10px"><span class="glyphicon glyphicon-move"></span></span> ',
            'items' => $items,
            'pluginEvents' => [
                'sortupdate' => 'function(e, ui) { jQuery.post(\'' . Url::to(['sort-category']) . '\', {id:ui.item.find(\'.podium-forum\').data(\'id\'), new:ui.item.index()}).done(function(data){ jQuery(\'#podiumSortInfo\').html(data); }).fail(function(){ jQuery(\'#podiumSortInfo\').html(\'<span class="text-danger">' . Yii::t('podium/view', 'Sorry! There was some error while changing the order of the categories.') . '</span>\'); }); }',
            ]
        ]); ?>
<?php endif;*/ ?>
        <h4>Forums not moderated by </h4>
        <?= Sortable::widget([
            'connected'=>true,
            'items'=>[
                ['content'=>'From Item 1'],
                ['content'=>'From Item 2'],
                ['content'=>'From Item 3'],
                ['content'=>'From Item 4'],
            ]
        ]); ?>
    </div>
    <div class="col-sm-1 text-center">
        <br>
        <h1><span class="glyphicon glyphicon-transfer"></span></h1>
    </div>
    <div class="col-sm-4">
        <h4 class="text-right">Forums moderated by </h4>
        <?= Sortable::widget([
            'connected'=>true,
            'itemOptions'=>['class'=>'alert alert-warning'],
            'items'=>[
                ['content'=>'To Item 1'],
                ['content'=>'To Item 2'],
                ['content'=>'To Item 3'],
                ['content'=>'To Item 4'],
            ]
        ]); ?>
    </div>
</div><br>
