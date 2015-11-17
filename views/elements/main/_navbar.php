<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */

use bizley\podium\components\Config;
use bizley\podium\components\PodiumUser;
use bizley\podium\Module as PodiumModule;
use bizley\podium\rbac\Rbac;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\Html;

$items = [['label' => Yii::t('podium/layout', 'Home'), 'url' => ['default/index']]];

$podiumUser   = new PodiumUser;
$podiumModule = PodiumModule::getInstance();

if (Yii::$app->user->isGuest) {
    if (Config::getInstance()->get('members_visible')) {
        $items[] = [
            'label'  => Yii::t('podium/layout', 'Members'), 
            'url'    => ['members/index'],
            'active' => $this->context->id == 'members'
        ];
    }
    if ($podiumModule->userComponent == PodiumModule::USER_OWN) {
        if (!empty($podiumModule->loginUrl)) {
            $items[] = ['label' => Yii::t('podium/layout', 'Sign in'), 'url' => $podiumModule->loginUrl];
        }
        if (!empty($podiumModule->registerUrl)) {
            $items[] = ['label' => Yii::t('podium/layout', 'Register'), 'url' => $podiumModule->registerUrl];
        }
    }
}
else {
    
    $messageCount      = $podiumUser->getNewMessagesCount();
    $subscriptionCount = $podiumUser->getSubscriptionsCount();
    
    if (Yii::$app->user->can(Rbac::ROLE_ADMIN)) {
        $items[] = [
            'label'  => Yii::t('podium/layout', 'Administration'), 
            'url'    => ['admin/index'],
            'active' => $this->context->id == 'admin'
        ];
    }
    $items[] = [
        'label'  => Yii::t('podium/layout', 'Members'), 
        'url'    => ['members/index'],
        'active' => $this->context->id == 'members'
    ];
    $items[] = [
        'label' => Yii::t('podium/layout', 'Profile') . ($subscriptionCount ? ' ' . Html::tag('span', $subscriptionCount, ['class' => 'badge']) : ''), 
        'url'   => ['profile/index'],
        'items' => [
            ['label' => Yii::t('podium/view', 'My Profile'), 'url' => ['profile/index']],
            ['label' => Yii::t('podium/view', 'Account Details'), 'url' => ['profile/details']],
            ['label' => Yii::t('podium/view', 'Forum Details'), 'url' => ['profile/forum']],
            ['label' => Yii::t('podium/view', 'Subscriptions'), 'url' => ['profile/subscriptions']],
        ]
    ];
    $items[] = [
        'label' => Yii::t('podium/layout', 'Messages') . ($messageCount ? ' ' . Html::tag('span', $messageCount, ['class' => 'badge']) : ''), 
        'url'   => ['messages/inbox'],
        'items' => [
            ['label' => Yii::t('podium/view', 'Inbox'), 'url' => ['messages/inbox']],
            ['label' => Yii::t('podium/view', 'Sent'), 'url' => ['messages/sent']],
            ['label' => Yii::t('podium/view', 'Deleted'), 'url' => ['messages/deleted']],
            ['label' => Yii::t('podium/view', 'New Message'), 'url' => ['messages/new']],
        ]
    ];
    if ($podiumModule->userComponent == PodiumModule::USER_OWN) {
        $items[] = ['label' => Yii::t('podium/layout', 'Sign out'), 'url' => ['profile/logout'], 'linkOptions' => ['data-method' => 'post']];
    }
}

NavBar::begin([
    'brandLabel'            => Config::getInstance()->get('name'),
    'brandUrl'              => ['default/index'],
    'options'               => ['class' => 'navbar-inverse navbar-default',],
    'innerContainerOptions' => ['class' => 'container-fluid',]
]);
echo Nav::widget([
    'options'         => ['class' => 'navbar-nav navbar-right'],
    'encodeLabels'    => false,
    'activateParents' => true,
    'items'           => $items,
]);
NavBar::end();