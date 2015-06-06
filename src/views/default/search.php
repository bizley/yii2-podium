<?php

use yii\helpers\Html;

?>
<?php if (!empty($dataProvider)): ?>
<?php
$this->title                   = Yii::t('podium/view', 'Search for "{query}" in {type}', ['query' => Html::encode($query), 'type' => $model->type == 'topics' ? 'threads' : 'posts']);
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Main Forum'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Search Forum'), 'url' => ['default/search']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-sm-12">
<?php switch ($model->display): ?>
<?php case 'topics': ?>
        <div class="panel-group" role="tablist">
            <?= $this->render('/elements/search/_forum_search_topics', ['dataProvider' => $dataProvider, 'query' => $query, 'type' => $model->type]) ?>
        </div>
<?php endswitch; ?>
    </div>
</div>
<?php else: ?>
<?php
$this->title                   = Yii::t('podium/view', 'Search Forum');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Main Forum'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
<?= $this->render('/elements/search/_search', ['model' => $model, 'list' => $list]) ?>
</div>
<?php endif;