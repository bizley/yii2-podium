<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

use bizley\podium\widgets\gridview\ActionColumn;
use bizley\podium\widgets\gridview\GridView;
use yii\grid\CheckboxColumn;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('podium/view', 'Moderators');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Administration Dashboard'), 'url' => ['admin/index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<?= $this->render('/elements/admin/_navbar', ['active' => 'mods']); ?>
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
        <ul class="nav nav-pills nav-stacked">
<?php foreach ($moderators as $moderator): ?>
            <li role="presentation" class="<?= $moderator->id == $mod->id ? 'active' : '' ?>">
                <a href="<?= Url::to(['admin/mods', 'id' => $moderator->id]) ?>">
                    <span class="glyphicon glyphicon-chevron-right"></span> <?= Html::encode($moderator->podiumName) ?>
                </a>
            </li>
<?php endforeach; ?>
        </ul>
    </div>
    <div class="col-sm-9">
        <h4><?= Yii::t('podium/view', 'List Forums') ?></h4>
        <?= Html::beginForm(); ?>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        [
            'class' => CheckboxColumn::className(),
            'headerOptions' => ['class' => 'col-sm-1 text-center'],
            'contentOptions' => ['class' => 'col-sm-1 text-center'],
            'checkboxOptions' => function($model) use ($mod) {
                return ['value' => $model->id, 'checked' => $model->isMod($mod->id)];
            }
        ],
        [
            'attribute' => 'id',
            'label' => Yii::t('podium/view', 'ID'),
            'contentOptions' => ['class' => 'col-sm-1 text-center'],
            'headerOptions' => ['class' => 'col-sm-1 text-center'],
        ],
        [
            'attribute' => 'name',
            'label' => Yii::t('podium/view', 'Name'),
            'format' => 'raw',
            'value' => function ($model) use ($mod) {
                return Html::encode($model->name) . ($model->isMod($mod->id) ? Html::hiddenInput('pre[]', $model->id) : '');
            },
        ],
        [
            'class' => ActionColumn::className(),
            'template' => '{mod}',
            'urlCreator' => function ($action, $model) use ($mod) {
                return Url::toRoute([$action, 'fid' => $model->id, 'uid' => $mod->id]);
            },
            'buttons' => [
                'mod' => function($url, $model) use ($mod) {
                    if ($model->isMod($mod->id)) {
                        return Html::a('<span class="glyphicon glyphicon-remove"></span> ' . Yii::t('podium/view', 'Remove'), $url, ActionColumn::buttonOptions([
                            'class' => 'btn btn-danger btn-xs',
                            'title' => Yii::t('podium/view', 'Remove from moderation list')
                        ]));
                    }
                    return Html::a('<span class="glyphicon glyphicon-plus"></span> ' . Yii::t('podium/view', 'Add'), $url, ActionColumn::buttonOptions([
                        'class' => 'btn btn-success btn-xs',
                        'title' => Yii::t('podium/view', 'Add to moderation list')
                    ]));
                },
            ],
        ]
    ],
]); ?>
        <?= Html::hiddenInput('mod_id', $mod->id) ?>
        <div class="row">
            <div class="col-sm-12">
                <?= Html::submitButton('<span class="glyphicon glyphicon-ok-sign"></span> ' . Yii::t('podium/view', 'Save Selected Moderation List'), [
                    'class' => 'btn btn-primary btn-sm',
                    'name' => 'save-button'
                ]) ?>
            </div>
        </div>
        <?= Html::endForm(); ?>
    </div>
</div><br>
<?php endif;
