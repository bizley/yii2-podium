<?php

use yii\helpers\Html;

echo Html::beginTag('ul', ['class' => 'nav nav-tabs']);
echo Html::tag('li', Html::a('<span class="glyphicon glyphicon-blackboard"></span> ' . Yii::t('podium/view', 'Dashboard'), ['index']), ['role' => 'presentation', 'class' => $active == 'index' ? 'active' : null]);
echo Html::tag('li', Html::a('<span class="glyphicon glyphicon-bullhorn"></span> ' . Yii::t('podium/view', 'Forums'), ['categories']), ['role' => 'presentation', 'class' => $active == 'categories' ? 'active' : null]);
echo Html::tag('li', Html::a('<span class="glyphicon glyphicon-user"></span> ' . Yii::t('podium/view', 'Members'), ['members']), ['role' => 'presentation', 'class' => $active == 'members' ? 'active' : null]);
echo Html::tag('li', Html::a('<span class="glyphicon glyphicon-cog"></span> ' . Yii::t('podium/view', 'Settings'), ['settings']), ['role' => 'presentation', 'class' => $active == 'settings' ? 'active' : null]);
echo Html::endTag('ul');