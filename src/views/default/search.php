<?php

use yii\helpers\Html;

?>
<?php if (!empty($dataProvider)): ?>
<?php
$title = 'Search for {type}';
if (!empty($query)) {
    $title .= ' with "{query}"';
}
if (!empty($author)) {
    $title .= ' by "{author}"';
}
$this->title                   = Yii::t('podium/view', $title, ['query' => Html::encode($query), 'author' => Html::encode($author), 'type' => $model->type == 'topics' ? 'threads' : 'posts']);
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Main Forum'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Search Forum'), 'url' => ['default/search']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-sm-12">
<?php switch ($model->display): ?>
<?php case 'topics': ?>
        <div class="panel-group" role="tablist">
            <?= $this->render('/elements/search/_forum_search_topics', ['dataProvider' => $dataProvider, 'query' => $query, 'author' => $author, 'type' => $model->type]) ?>
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