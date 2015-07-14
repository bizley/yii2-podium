<?php

use yii\helpers\Html;

echo Html::beginTag('ul', ['class' => 'nav nav-tabs']);
echo Html::tag('li', Html::a('<span class="glyphicon glyphicon-blackboard"></span> ' . Yii::t('podium/view', 'Dashboard'), ['admin/index']), ['role' => 'presentation', 'class' => $active == 'index' ? 'active' : null]);
echo Html::tag('li', Html::a('<span class="glyphicon glyphicon-bullhorn"></span> ' . Yii::t('podium/view', 'Forums'), ['admin/categories']), ['role' => 'presentation', 'class' => $active == 'categories' ? 'active' : null]);
echo Html::tag('li', Html::a('<span class="glyphicon glyphicon-user"></span> ' . Yii::t('podium/view', 'Members'), ['admin/members']), ['role' => 'presentation', 'class' => $active == 'members' ? 'active' : null]);
echo Html::tag('li', Html::a('<span class="glyphicon glyphicon-scissors"></span> ' . Yii::t('podium/view', 'Moderators'), ['admin/mods']), ['role' => 'presentation', 'class' => $active == 'mods' ? 'active' : null]);
echo Html::tag('li', Html::a('<span class="glyphicon glyphicon-text-color"></span> ' . Yii::t('podium/view', 'Contents'), ['admin/contents']), ['role' => 'presentation', 'class' => $active == 'contents' ? 'active' : null]);
echo Html::tag('li', Html::a('<span class="glyphicon glyphicon-cog"></span> ' . Yii::t('podium/view', 'Settings'), ['admin/settings']), ['role' => 'presentation', 'class' => $active == 'settings' ? 'active' : null]);
echo Html::tag('li', Html::a('<span class="glyphicon glyphicon-filter"></span> ' . Yii::t('podium/view', 'Logs'), ['admin/logs']), ['role' => 'presentation', 'class' => $active == 'logs' ? 'active' : null]);
echo Html::endTag('ul');