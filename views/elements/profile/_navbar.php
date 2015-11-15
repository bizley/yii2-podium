<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
use bizley\podium\components\PodiumUser;
use yii\helpers\Html;

$podiumUser        = new PodiumUser;
$messageCount      = $podiumUser->getNewMessagesCount();
$subscriptionCount = $podiumUser->getSubscriptionsCount();

echo Html::beginTag('ul', ['class' => 'nav nav-pills nav-stacked']);
echo Html::tag('li', Html::a(Yii::t('podium/view', 'My Profile'), ['profile/index']), ['role' => 'presentation', 'class' => $active == 'profile' ? 'active' : null]);
echo Html::tag('li', Html::a(Yii::t('podium/view', 'Account Details'), ['profile/details']), ['role' => 'presentation', 'class' => $active == 'details' ? 'active' : null]);
echo Html::tag('li', Html::a(Yii::t('podium/view', 'Forum Details'), ['profile/forum']), ['role' => 'presentation', 'class' => $active == 'forum' ? 'active' : null]);
echo Html::tag('li', Html::a(($messageCount ? Html::tag('span', $messageCount, ['class' => 'badge pull-right']) : '') . Yii::t('podium/view', 'Messages'), ['messages/inbox']), ['role' => 'presentation', 'class' => $active == 'messages' ? 'active' : null]);
echo Html::tag('li', Html::a(($subscriptionCount ? Html::tag('span', $subscriptionCount, ['class' => 'badge pull-right']) : '') . Yii::t('podium/view', 'Subscriptions'), ['profile/subscriptions']), ['role' => 'presentation', 'class' => $active == 'subscriptions' ? 'active' : null]);
echo Html::endTag('ul');