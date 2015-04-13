<?php

use yii\helpers\Html;

$this->title                   = Html::encode($model->name);
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Main Forum'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Html::encode($category->name), 'url' => ['category', 'id' => $category->id, 'slug' => $category->slug]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-sm-12">
        <div class="panel-group" role="tablist" aria-multiselectable="true">
            <?= $this->render('/elements/forum/_forum_section', ['model' => $model]) ?>
        </div>
    </div>
</div>