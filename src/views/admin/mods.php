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
    $this->registerJs('jQuery(\'[data-toggle="tooltip"]\').tooltip()', View::POS_READY, 'bootstrap-tooltip');
}

?>

<br>

<?php if (empty($moderators)): ?>
<div class="row">
    <div class="col-sm-12">
        <h3><?= Yii::t('podium/view', 'No moderators have been added yet.') ?></h3>
    </div>
</div>
<?php else: ?>

<div class="row">
    <br>
    <div class="col-sm-3">
        <?= Html::beginTag('ul', ['class' => 'nav nav-pills nav-stacked']); ?>
<?php foreach ($moderators as $moderator): ?>
        <?= Html::tag('li', Html::a(Html::tag('span', '', ['class' => 'glyphicon glyphicon-chevron-right']) . ' ' . Html::encode($moderator->getPodiumName()), ['mods', 'id' => $moderator->id]), ['role' => 'presentation', 'class' => $moderator->id == $mod->id ? 'active' : null]); ?>
<?php endforeach; ?>
        <?= Html::endTag('ul'); ?>
    </div>
    <div class="col-sm-9">
        <div class="row">
            <div class="col-sm-12" id="podiumModInfo">
                <span class="text-info"><?= Yii::t('podium/view', 'Drag and drop forums to select which ones are moderated by this user.') ?></span>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-5">
                <h4><?= Yii::t('podium/view', 'Forums not moderated by {name}', ['name' => $mod->getPodiumName()]) ?></h4>
                <?= Sortable::widget([
                    'options' => ['id' => 'forumsNotModerated'],
                    'connected' => true,
                    'items' => $forums,
                    'pluginEvents' => [
                        'sortstart' => 'function(e, ui) { console.log(1, ui); }',
                        'sortupdate' => 'function(e, ui) { console.log(2, ui); }',
                    ]
                ]); ?>
            </div>
            <div class="col-sm-1 text-center">
                <br>
                <h1><span class="glyphicon glyphicon-transfer"></span></h1>
            </div>
            <div class="col-sm-5">
                <h4 class="text-right"><?= Yii::t('podium/view', 'Forums moderated by {name}', ['name' => $mod->getPodiumName()]) ?></h4>
                <?= Sortable::widget([
                    'options' => ['id' => 'forumsModerated'],
                    'connected' => true,
                    'itemOptions' => ['class' => 'alert alert-info'],
                    'items' => $moderated,
                    'pluginEvents' => [
                        'sortstart' => 'function(e, ui) { console.log(3, ui); }',
                        'sortupdate' => 'function(e, ui) { console.log(4, ui); }',
                    ]
                ]); ?>
            </div>
        </div>
    </div>
</div><br>

<?php endif; ?>