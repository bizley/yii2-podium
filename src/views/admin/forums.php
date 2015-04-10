<?php

use kartik\sortable\Sortable;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$this->title = Yii::t('podium/view', 'Forums');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Administration Dashboard'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

echo $this->render('/elements/admin/_navbar', ['active' => 'forums']);

$items = [];
foreach ($dataProvider as $forum) {
    $items[] = [
        'content' => Html::tag('p', 
                Html::a(Html::tag('span', '', ['class' => 'glyphicon glyphicon-cog']) . ' ' . Yii::t('podium/view', 'Edit Forum'), ['edit-forum', 'id' => $forum->id], ['class' => 'btn btn-success btn-xs']) . 
                ' ' . Html::tag('button', Html::tag('span', '', ['class' => 'glyphicon glyphicon-trash']) . ' ' . Yii::t('podium/view', 'Delete Forum'), ['class' => 'btn btn-danger btn-xs', 'data-url' => Url::to(['delete-forum', 'id' => $forum->id]), 'data-toggle' => 'modal', 'data-target' => '#podiumModalDelete']), 
                ['class' => 'pull-right']) . 
                Html::tag('span', Html::encode($forum->name), ['class' => 'podium-forum', 'data-id' => $forum->id]) . 
                (!empty($forum->sub) ? '<br>' . Html::tag('small', Html::encode($forum->sub), ['class' => 'text-muted']) : ''),
    ];
}

if (!empty($items)) {
    $this->registerJs('jQuery(\'#podiumModalDelete\').on(\'show.bs.modal\', function(e) {
    var button = jQuery(e.relatedTarget);
    jQuery(\'#deleteUrl\').attr(\'href\', button.data(\'url\'));
});', View::POS_READY, 'bootstrap-modal-delete');
}

?>

<br>

<div class="row">
    <div class="col-sm-12 text-right">
        <p class="pull-left" id="podiumSortInfo"></p>
        <a href="<?= Url::to(['new-forum']) ?>" class="btn btn-primary"><span class="glyphicon glyphicon-plus"></span> Create new forum</a>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <br>
<?php if (empty($items)): ?>
        <h3><?= Yii::t('podium/view', 'No forums have been added yet.') ?></h3>
<?php else: ?>
        <?= Sortable::widget([
            'showHandle' => true,
            'handleLabel' => '<span class="btn btn-default btn-xs pull-left" style="margin-right:10px"><span class="glyphicon glyphicon-move"></span></span> ',
            'items' => $items,
            'pluginEvents' => [
                'sortupdate' => 'function(e, ui) { jQuery.post(\'' . Url::to(['sort']) . '\', {id:ui.item.find(\'.podium-forum\').data(\'id\'), new:ui.item.index()}).done(function(data){ jQuery(\'#podiumSortInfo\').html(data); }).fail(function(){ jQuery(\'#podiumSortInfo\').html(\'<span class="text-danger">' . Yii::t('podium/view', 'Sorry! There was some error while changing the order of the forums.') . '</span>\'); }); }',
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