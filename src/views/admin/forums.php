<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

use bizley\podium\helpers\Helper;
use bizley\podium\widgets\Modal;
use kartik\sortable\Sortable;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('podium/view', 'Forums');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Administration Dashboard'), 'url' => ['admin/index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Categories List'), 'url' => ['admin/categories']];
$this->params['breadcrumbs'][] = $this->title;

$items = [];
foreach ($forums as $forum) {
    $items[] = ['content' => Helper::adminForumsPrepareContent($forum)];
}

if (!empty($items)) {
    $this->registerJs("$('#podiumModalDelete').on('show.bs.modal', function(e) { var button = $(e.relatedTarget); $('#deleteUrl').attr('href', button.data('url')); });");
    $this->registerJs("$('[data-toggle=\"tooltip\"]').tooltip();");
}

?>
<?= $this->render('/elements/admin/_navbar', ['active' => 'categories']); ?>
<br>
<div class="row">
    <div class="col-md-3 col-sm-4">
        <ul class="nav nav-pills nav-stacked">
            <li role="presentation"><a href="<?= Url::to(['admin/categories']) ?>"><span class="glyphicon glyphicon-list"></span> <?= Yii::t('podium/view', 'Categories List') ?></a></li>
<?php foreach ($categories as $category): ?>
            <li role="presentation" class="<?= $model->id == $category->id ? 'active' : '' ?>"><a href="<?= Url::to(['admin/forums', 'cid' => $category->id]) ?>"><span class="glyphicon glyphicon-chevron-<?= $category->id == $model->id ? 'down' : 'right' ?>"></span> <?= Html::encode($category->name) ?></a></li>
<?php if ($category->id == $model->id): ?>
<?php foreach ($forums as $forum): ?>
            <li role="presentation"><a href="<?= Url::to(['admin/edit-forum', 'id' => $forum->id, 'cid' => $forum->category_id]) ?>"><span class="glyphicon glyphicon-bullhorn"></span> <?= Html::encode($forum->name) ?></a></li>
<?php endforeach; ?>
            <li role="presentation"><a href="<?= Url::to(['admin/new-forum', 'cid' => $category->id]) ?>"><span class="glyphicon glyphicon-plus-sign"></span> <?= Yii::t('podium/view', 'Create new forum') ?></a></li>
<?php endif; ?>
<?php endforeach; ?>
            <li role="presentation"><a href="<?= Url::to(['admin/new-category']) ?>"><span class="glyphicon glyphicon-plus"></span> <?= Yii::t('podium/view', 'Create new category') ?></a></li>
        </ul>
    </div>
    <div class="col-md-9 col-sm-8">
        <div class="row">
            <div class="col-sm-12 text-right">
                <p class="pull-left" id="podiumSortInfo"></p>
                <a href="<?= Url::to(['admin/new-forum', 'cid' => $model->id]) ?>" class="btn btn-primary"><span class="glyphicon glyphicon-plus-sign"></span> <?= Yii::t('podium/view', 'Create new forum') ?></a>
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
                'sortupdate' => 'function(e, ui) { $.post(\'' . Url::to(['admin/sort-forum']) . '\', {id:ui.item.find(\'.podium-forum\').data(\'id\'), category:ui.item.find(\'.podium-forum\').data(\'category\'),new:ui.item.index()}).done(function(data){ $(\'#podiumSortInfo\').html(data); }).fail(function(){ $(\'#podiumSortInfo\').html(\'<span class="text-danger">' . Yii::t('podium/view', 'Sorry! There was some error while changing the order of the forums.') . '</span>\'); }); }',
            ]
        ]); ?>
<?php endif; ?>
    </div>
</div><br>

<?php if (!empty($items)): ?>
<?php Modal::begin([
    'id' => 'podiumModalDelete',
    'header' => Yii::t('podium/view', 'Delete Forum'),
    'footer' => Yii::t('podium/view', 'Delete Forum'),
    'footerConfirmOptions' => ['class' => 'btn btn-danger', 'id' => 'deleteUrl']
 ]) ?>
<p><?= Yii::t('podium/view', 'Are you sure you want to delete this forum?') ?></p>
<p><?= Yii::t('podium/view', "All forum's threads and posts will be deleted as well.") ?></p>
<p><strong><?= Yii::t('podium/view', 'This action can not be undone.') ?></strong></p>
<?php Modal::end() ?>
<?php endif;
