<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
use kartik\sortable\Sortable;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$this->title = Yii::t('podium/view', 'Forums');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Administration Dashboard'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Categories List'), 'url' => ['categories']];
$this->params['breadcrumbs'][] = $this->title;

echo $this->render('/elements/admin/_navbar', ['active' => 'categories']);

function prepareContent($forum) {
    $actions = [];
    $actions[] = Html::button(Html::tag('span', '', ['class' => 'glyphicon glyphicon-eye-' . ($forum->visible ? 'open' : 'close')]), ['class' => 'btn btn-xs text-muted', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', $forum->visible ? 'Forum visible for guests (if category is visible)' : 'Forum hidden for guests')]);
    $actions[] = Html::a(Html::tag('span', '', ['class' => 'glyphicon glyphicon-cog']), ['edit-forum', 'id' => $forum->id, 'cid' => $forum->category_id], ['class' => 'btn btn-default btn-xs', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'Edit Forum')]);
    $actions[] = Html::tag('span', Html::button(Html::tag('span', '', ['class' => 'glyphicon glyphicon-trash']), ['class' => 'btn btn-danger btn-xs', 'data-url' => Url::to(['delete-forum', 'id' => $forum->id, 'cid' => $forum->category_id]), 'data-toggle' => 'modal', 'data-target' => '#podiumModalDelete']), ['data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'Delete Forum')]);

    return Html::tag('p', implode(' ', $actions), ['class' => 'pull-right']) . Html::tag('span', Html::encode($forum->name), ['class' => 'podium-forum', 'data-id' => $forum->id, 'data-category' => $forum->category_id]);
}

$items = [];
foreach ($forums as $forum) {
    $items[] = [
        'content' => prepareContent($forum),
    ];
}

if (!empty($items)) {
    $this->registerJs('jQuery(\'#podiumModalDelete\').on(\'show.bs.modal\', function(e) {
    var button = jQuery(e.relatedTarget);
    jQuery(\'#deleteUrl\').attr(\'href\', button.data(\'url\'));
});', View::POS_READY, 'bootstrap-modal-delete');
    $this->registerJs('jQuery(\'[data-toggle="tooltip"]\').tooltip()', View::POS_READY, 'bootstrap-tooltip');
}

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
        <div class="row">
            <div class="col-sm-12 text-right">
                <p class="pull-left" id="podiumSortInfo"></p>
                <a href="<?= Url::to(['new-forum', 'cid' => $model->id]) ?>" class="btn btn-primary"><span class="glyphicon glyphicon-plus-sign"></span> <?= Yii::t('podium/view', 'Create new forum') ?></a>
            </div>
        </div>
        <br>
<?php if (empty($items)): ?>
        <h3><?= Yii::t('podium/view', 'No forums have been added yet in this category.') ?></h3>
<?php else: ?>
        <?= Sortable::widget([
            'showHandle' => true,
            'handleLabel' => '<span class="btn btn-default btn-xs pull-left" style="margin-right:10px"><span class="glyphicon glyphicon-move"></span></span> ',
            'items' => $items,
            'pluginEvents' => [
                'sortupdate' => 'function(e, ui) { jQuery.post(\'' . Url::to(['sort-forum']) . '\', {id:ui.item.find(\'.podium-forum\').data(\'id\'), category:ui.item.find(\'.podium-forum\').data(\'category\'),new:ui.item.index()}).done(function(data){ jQuery(\'#podiumSortInfo\').html(data); }).fail(function(){ jQuery(\'#podiumSortInfo\').html(\'<span class="text-danger">' . Yii::t('podium/view', 'Sorry! There was some error while changing the order of the forums.') . '</span>\'); }); }',
            ]
        ]); ?>
<?php endif; ?>
    </div>
</div><br>

<?php if (!empty($items)): ?>
<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="podiumModalDeleteLabel" aria-hidden="true" id="podiumModalDelete">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="podiumModalDeleteLabel"><?= Yii::t('podium/view', 'Delete Forum') ?></h4>
            </div>
            <div class="modal-body">
                <p><?= Yii::t('podium/view', 'Are you sure you want to delete this forum?') ?></p>
                <p><?= Yii::t('podium/view', 'All forum\'s threads and posts will be deleted as well.') ?></p>
                <p><strong><?= Yii::t('podium/view', 'This action can not be undone.') ?></strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= Yii::t('podium/view', 'Cancel') ?></button>
                <a href="#" id="deleteUrl" class="btn btn-danger"><?= Yii::t('podium/view', 'Delete Forum') ?></a>
            </div>
        </div>
    </div>
</div>
<?php endif;