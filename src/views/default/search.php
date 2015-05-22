<?php

$this->title                   = Yii::t('podium/view', 'Search Forum');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Main Forum'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-sm-12">
        <div class="panel-group" role="tablist">
            <?= $this->render('/elements/search/_forum_search', ['dataProvider' => $dataProvider]) ?>
        </div>
    </div>
</div>