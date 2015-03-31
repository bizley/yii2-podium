<?php

use bizley\ajaxdropdown\AjaxDropdown;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use Zelenin\yii\widgets\Summernote\Summernote;

$this->title                   = Yii::t('podium/view', 'View Message');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'My Profile'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs('$(\'[data-toggle="tooltip"]\').tooltip()', View::POS_READY, 'bootstrap-tooltip');

?>
<div class="row">
    <div class="col-sm-3">
        <?= $this->render('/elements/profile/_navbar', ['active' => 'messages']) ?>
    </div>
    <div class="col-sm-9">
        
        <?= $this->render('/elements/messages/_navbar', ['active' => 'view']) ?>
        
        <br>
        
        <div class="col-sm-3">
            autor
        </div>
        <div class="col-sm-9">
            <div class="popover right podium">
                <div class="arrow"></div>
                <div class="popover-title">
                    <small class="pull-right"><?= Html::tag('span', Yii::$app->formatter->asRelativeTime($model->created_at), ['data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::$app->formatter->asDatetime($model->created_at, 'long')]); ?></small>
                    <?= Html::encode($model->topic) ?>
                </div>
                <div class="popover-content">
                    <?= $model->content ?>
                    <div class="text-right">
<?php if ($model->receiver_id == Yii::$app->user->id): ?>
                        <?= Html::a('<span class="glyphicon glyphicon-share-alt"></span>', ['reply', 'id' => $model->id], ['class' => 'btn btn-success btn-xs', 'data-toggle' => 'tooltip', 'data-placement' => 'bottom', 'title' => Yii::t('podium/view', 'Reply to Message')]) ?>
<?php endif; ?>
                        <?= Html::a('<span class="glyphicon glyphicon-trash"></span>', ['delete', 'id' => $model->id], ['class' => 'btn btn-danger btn-xs', 'data-toggle' => 'tooltip', 'data-placement' => 'bottom', 'title' => Yii::t('podium/view', 'Delete Message')]) ?>
                    </div>
                </div>
            </div>
        </div>        
        
    </div>
</div><br>