<?php

use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\helpers\Html;
use yii\web\View;
use yii\helpers\ArrayHelper;
use bizley\podium\models\User;
use bizley\podium\components\Helper;

$this->title = Yii::t('podium/view', 'Forums');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Administration Dashboard'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

echo $this->render('/elements/admin/_navbar', ['active' => 'forums']);
?>

<br>

