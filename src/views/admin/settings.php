<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;

$this->title                   = Yii::t('podium/view', 'Forum Settings');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Administration Dashboard'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

echo  $this->render('/elements/admin/_navbar', ['active' => 'settings']); ?>

Dashboard