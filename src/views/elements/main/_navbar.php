<?php

use bizley\podium\components\Config;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\Html;

$items = [
    ['label' => Yii::t('podium/layout', 'Home'), 'url' => ['default/index']],
];
if (Yii::$app->user->isGuest) {
    if (Config::getInstance()->get('members_visible')) {
        $items[] = ['label' => Yii::t('podium/layout', 'Members'), 'url' => ['members/index']];
    }
    $items[] = ['label' => Yii::t('podium/layout', 'Sign in'), 'url' => ['account/login']];
    $items[] = ['label' => Yii::t('podium/layout', 'Register'), 'url' => ['account/register']];
}
else {
    
    $messageCount = Yii::$app->user->getIdentity()->getNewMessagesCount();
    
    if (Yii::$app->user->can('settings')) {
        $items[] = ['label' => Yii::t('podium/layout', 'Administration'), 'url' => ['admin/index']];
    }
    $items[] = ['label' => Yii::t('podium/layout', 'Members'), 'url' => ['members/index']];
    $items[] = [
        'label' => Yii::t('podium/layout', 'Profile'), 
        'url' => ['profile/index'],
        'items' => [
            ['label' => Yii::t('podium/view', 'My Profile'), 'url' => ['profile/index']],
            ['label' => Yii::t('podium/view', 'Account Details'), 'url' => ['profile/details']],
            ['label' => Yii::t('podium/view', 'Forum Details'), 'url' => ['profile/forum']],
            ['label' => Yii::t('podium/view', 'Subscriptions') . ' ' . Html::tag('span', '0', ['class' => 'badge']), 'url' => ['profile/subscriptions']],
        ]
    ];
    $items[] = [
        'label' => Yii::t('podium/layout', 'Messages') . ($messageCount ? ' ' . Html::tag('span', $messageCount, ['class' => 'badge']) : ''), 
        'url' => ['messages/inbox'],
        'items' => [
            ['label' => Yii::t('podium/view', 'Inbox'), 'url' => ['messages/inbox']],
            ['label' => Yii::t('podium/view', 'Sent'), 'url' => ['messages/sent']],
            ['label' => Yii::t('podium/view', 'Deleted'), 'url' => ['messages/deleted']],
            ['label' => Yii::t('podium/view', 'New Message'), 'url' => ['messages/new']],
        ]
    ];
    $items[] = ['label' => Yii::t('podium/layout', 'Sign out'), 'url' => ['profile/logout'], 'linkOptions' => ['data-method' => 'post']];
}

NavBar::begin([
    'brandLabel'            => Config::getInstance()->get('name'),
    'brandUrl'              => ['default/index'],
    'options'               => ['class' => 'navbar-inverse navbar-default',],
    'innerContainerOptions' => ['class' => 'container-fluid',]
]);
echo Nav::widget([
    'options' => ['class' => 'navbar-nav navbar-right'],
    'encodeLabels' => false,
    'activateParents' => true,
    'items'   => $items,
]);
NavBar::end();