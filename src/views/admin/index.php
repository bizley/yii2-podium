<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;

$this->title                   = Yii::t('podium/view', 'Administration Dashboard');
$this->params['breadcrumbs'][] = $this->title;

echo  $this->render('/elements/admin/_navbar', ['active' => 'index']); ?>

Dashboard