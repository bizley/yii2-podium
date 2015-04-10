<?php

use kartik\sortable\Sortable;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$this->title = Yii::t('podium/view', 'Forums');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Administration Dashboard'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

echo $this->render('/elements/admin/_navbar', ['active' => 'forums']);

$this->registerJs('$(\'#podiumModalDelete\').on(\'show.bs.modal\', function(e) {
    var button = $(e.relatedTarget);
    $(\'#deleteUrl\').attr(\'href\', button.data(\'url\'));
});', View::POS_READY, 'bootstrap-modal-delete');

$items = [];
foreach ($dataProvider as $forum) {
    $items[] = [
        'content' => Html::tag('p', 
                Html::a(Html::tag('span', '', ['class' => 'glyphicon glyphicon-cog']) . ' ' . Yii::t('podium/view', 'Edit Forum'), ['edit-forum', 'id' => $forum->id], ['class' => 'btn btn-success btn-xs']) . 
                ' ' . Html::tag('button', Html::tag('span', '', ['class' => 'glyphicon glyphicon-trash']) . ' ' . Yii::t('podium/view', 'Delete Forum'), ['class' => 'btn btn-danger btn-xs', 'data-url' => Url::to(['delete-forum', 'id' => $forum->id]), 'data-toggle' => 'modal', 'data-target' => '#podiumModalDelete']), 
                ['class' => 'pull-right']) . 
                Html::encode($forum->name) . 
                (!empty($forum->sub) ? '<br>' . Html::tag('small', Html::encode($forum->sub), ['class' => 'text-muted']) : ''),
    ];
}

?>

<br>

<div class="row">
    <div class="col-sm-12 text-right">
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
            'items' => $items
        ]); ?>
<?php endif; ?>
    </div>
</div><br>