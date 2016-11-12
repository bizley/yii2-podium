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
use yii\helpers\Url;

$this->title = Yii::t('podium/view', 'Forums');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Administration Dashboard'), 'url' => ['admin/index']];
$this->params['breadcrumbs'][] = $this->title;

$items = [];
foreach ($dataProvider as $category) {
    $items[] = ['content' => Helper::adminCategoriesPrepareContent($category)];
}

if (!empty($items)) {
    $this->registerJs("$('#podiumModalDelete').on('show.bs.modal', function(e) { var button = $(e.relatedTarget); $('#deleteUrl').attr('href', button.data('url')); });");
    $this->registerJs("$('[data-toggle=\"tooltip\"]').tooltip();");
}

?>
<?= $this->render('/elements/admin/_navbar', ['active' => 'categories']); ?>
<br>
<div class="row">
    <div class="col-sm-12 text-right">
        <p class="pull-left" id="podiumSortInfo"></p>
        <a href="<?= Url::to(['admin/new-category']) ?>" class="btn btn-primary"><span class="glyphicon glyphicon-plus"></span> <?= Yii::t('podium/view', 'Create new category') ?></a>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <br>
<?php if (empty($items)): ?>
        <h3><?= Yii::t('podium/view', 'No categories have been added yet.') ?></h3>
<?php else: ?>
        <?= Sortable::widget([
            'showHandle'   => true,
            'handleLabel'  => '<span class="btn btn-default btn-xs pull-left" style="margin-right:10px"><span class="glyphicon glyphicon-move"></span></span> ',
            'items'        => $items,
            'pluginEvents' => [
                'sortupdate' => 'function(e, ui) { $.post(\'' . Url::to(['admin/sort-category']) . '\', {id:ui.item.find(\'.podium-forum\').data(\'id\'), new:ui.item.index()}).done(function(data){ $(\'#podiumSortInfo\').html(data); }).fail(function(){ $(\'#podiumSortInfo\').html(\'<span class="text-danger">' . Yii::t('podium/view', 'Sorry! There was some error while changing the order of the categories.') . '</span>\'); }); }',
            ]
        ]); ?>
<?php endif; ?>
    </div>
</div><br>

<?php if (!empty($items)): ?>
<?php Modal::begin([
    'id' => 'podiumModalDelete',
    'header' => Yii::t('podium/view', 'Delete Category'),
    'footer' => Yii::t('podium/view', 'Delete Category'),
    'footerConfirmOptions' => ['class' => 'btn btn-danger', 'id' => 'deleteUrl']
 ]) ?>
<p><?= Yii::t('podium/view', 'Are you sure you want to delete this category?') ?></p>
<p><?= Yii::t('podium/view', "All category forums, forums' threads and posts will be deleted as well.") ?></p>
<p><strong><?= Yii::t('podium/view', 'This action can not be undone.') ?></strong></p>
<?php Modal::end() ?>
<?php endif;
