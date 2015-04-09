<?php

use kartik\sortable\Sortable;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('podium/view', 'Forums');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Administration Dashboard'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

echo $this->render('/elements/admin/_navbar', ['active' => 'forums']);

$items = [];
foreach ($dataProvider as $forum) {
    $items[] = [
        'content' => Html::tag('p', 
                Html::a(Html::tag('span', '', ['class' => 'glyphicon glyphicon-cog']), ['edit-forum', 'id' => $forum->id], ['class' => 'btn btn-success btn-xs']), 
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
</div>