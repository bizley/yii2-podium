<?php

use bizley\podium\components\Helper;
use bizley\podium\widgets\PageSizer;
use yii\grid\ActionColumn;
use yii\grid\CheckboxColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;

$this->title = Yii::t('podium/view', 'Moderators');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Administration Dashboard'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

echo $this->render('/elements/admin/_navbar', ['active' => 'mods']);

$this->registerJs('jQuery(\'[data-toggle="tooltip"]\').tooltip()', View::POS_READY, 'bootstrap-tooltip');

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
        <h4><?= Yii::t('podium/view', 'List of Forums') ?></h4>
        <?php Pjax::begin(); ?>
        <?= PageSizer::widget() ?>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel'  => $searchModel,
            'filterSelector' => 'select#per-page',
            'tableOptions' => ['class' => 'table table-striped table-hover'],
            'columns'      => [
                [
                    'class'              => CheckboxColumn::className(),
                    'headerOptions'      => ['class' => 'col-sm-1 text-center'],
                    'contentOptions'     => ['class' => 'col-sm-1 text-center'],
                    'checkboxOptions' => function($model) use($mod) {
                        return ['value' => $model->id, 'checked' => $model->isMod($mod->id)];
                    }
                ],
                [
                    'attribute'          => 'id',
                    'label'              => Yii::t('podium/view', 'ID') . Helper::sortOrder('id'),
                    'encodeLabel'        => false,
                    'contentOptions'     => ['class' => 'col-sm-1 text-center'],
                    'headerOptions'      => ['class' => 'col-sm-1 text-center'],
                ],
                [
                    'attribute'          => 'name',
                    'label'              => Yii::t('podium/view', 'Name') . Helper::sortOrder('name'),
                    'encodeLabel'        => false,
                ],
                [
                    'class'          => ActionColumn::className(),
                    'header'         => Yii::t('podium/view', 'Actions'),
                    'contentOptions' => ['class' => 'text-right'],
                    'headerOptions'  => ['class' => 'text-right'],
                    'template'       => '{mod}',
                    'buttons'        => [
                        'mod' => function($url, $model) use($mod) {
                            if ($model->isMod($mod->id)) {
                                return Html::tag('span', Html::tag('button', '<span class="glyphicon glyphicon-remove"></span> ' . Yii::t('podium/view', 'Remove'), ['class' => 'btn btn-danger btn-xs', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'Remove from moderation list')]), ['data-toggle' => 'modal', 'data-target' => '#podiumModalBan', 'data-url' => $url]);
                            }
                            else {
                                return Html::tag('span', Html::tag('button', '<span class="glyphicon glyphicon-ok"></span> ' . Yii::t('podium/view', 'Add'), ['class' => 'btn btn-success btn-xs', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'Add to moderation list')]), ['data-toggle' => 'modal', 'data-target' => '#podiumModalUnBan', 'data-url' => $url]);
                            }
                        },
                    ],
                ]
            ],
        ]); ?>
        <?php Pjax::end(); ?>

    </div>
</div><br>

<?php endif; ?>