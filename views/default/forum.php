<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title                   = Html::encode($model->name);
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Main Forum'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Html::encode($category->name), 'url' => ['category', 'id' => $category->id, 'slug' => $category->slug]];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php if (Yii::$app->user->can('createPodiumThread')): ?>
<div class="row">
    <div class="col-sm-12 text-right">
        <a href="<?= Url::to(['new-thread', 'cid' => $category->id, 'fid' => $model->id]) ?>" class="btn btn-primary"><span class="glyphicon glyphicon-plus"></span> <?= Yii::t('podium/view', 'Create new thread') ?></a>
        <br><br>
    </div>
</div>
<?php endif; ?>
<div class="row">
    <div class="col-sm-12">
        <div class="panel-group" role="tablist">
            <?= $this->render('/elements/forum/_forum_section', ['model' => $model]) ?>
        </div>
    </div>
</div>