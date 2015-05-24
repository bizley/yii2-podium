<?php

$this->title                   = Yii::t('podium/view', 'Search Forum');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Main Forum'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php if (!empty($dataProvider)): ?>
<div class="row">
    <div class="col-sm-12">
        <div class="panel-group" role="tablist">
            <?= $this->render('/elements/search/_forum_search', ['dataProvider' => $dataProvider, 'query' => $query]) ?>
        </div>
    </div>
</div>
<?php else: ?>
<div class="row">
<?= $this->render('/elements/search/_search', ['model' => $model]) ?>
</div>
<?php endif;