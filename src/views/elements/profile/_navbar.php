<?php
use yii\helpers\Html;

$messageCount = Yii::$app->user->getIdentity()->getNewMessagesCount();

echo Html::beginTag('ul', ['class' => 'nav nav-pills nav-stacked']);
echo Html::tag('li', Html::a(Yii::t('podium/view', 'My Profile'), ['profile/index']), ['role' => 'presentation', 'class' => $active == 'profile' ? 'active' : null]);
echo Html::tag('li', Html::a(Yii::t('podium/view', 'Account Details'), ['profile/details']), ['role' => 'presentation', 'class' => $active == 'details' ? 'active' : null]);
echo Html::tag('li', Html::a(Yii::t('podium/view', 'Forum Details'), ['profile/forum']), ['role' => 'presentation', 'class' => $active == 'forum' ? 'active' : null]);
echo Html::tag('li', Html::a(($messageCount ? Html::tag('span', $messageCount, ['class' => 'badge pull-right']) : '') . Yii::t('podium/view', 'Messages'), ['messages/inbox']), ['role' => 'presentation', 'class' => $active == 'messages' ? 'active' : null]);
echo Html::tag('li', Html::a(Html::tag('span', '0', ['class' => 'badge pull-right']) . Yii::t('podium/view', 'Subscriptions'), ['subscriptions']), ['role' => 'presentation', 'class' => $active == 'subscriptions' ? 'active' : null]);
echo Html::endTag('ul');