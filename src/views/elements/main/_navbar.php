<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

use bizley\podium\models\User;
use bizley\podium\Module as Podium;
use bizley\podium\rbac\Rbac;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\Html;

$items = [['label' => Yii::t('podium/view', 'Home'), 'url' => ['default/index']]];

$podiumModule = Podium::getInstance();

if (Yii::$app->user->isGuest) {
    if (Podium::getInstance()->config->get('members_visible')) {
        $items[] = [
            'label'  => Yii::t('podium/view', 'Members'), 
            'url'    => ['members/index'],
            'active' => $this->context->id == 'members'
        ];
    }
    if ($podiumModule->userComponent == Podium::USER_OWN) {
        if (!empty($podiumModule->registerUrl)) {
            $items[] = ['label' => Yii::t('podium/view', 'Register'), 'url' => $podiumModule->registerUrl];
        }
        if (!empty($podiumModule->loginUrl)) {
            $items[] = ['label' => Yii::t('podium/view', 'Sign in'), 'url' => $podiumModule->loginUrl];
        }
    }
}
else {
    $podiumUser        = User::findMe();
    $messageCount      = $podiumUser->newMessagesCount;
    $subscriptionCount = $podiumUser->subscriptionsCount;
    
    if (User::can(Rbac::ROLE_ADMIN)) {
        $items[] = [
            'label'  => Yii::t('podium/view', 'Administration'), 
            'url'    => ['admin/index'],
            'active' => $this->context->id == 'admin'
        ];
    }
    $items[] = [
        'label'  => Yii::t('podium/view', 'Members'), 
        'url'    => ['members/index'],
        'active' => $this->context->id == 'members'
    ];
    $items[] = [
        'label' => Yii::t('podium/view', 'Profile ({name})', ['name' => $podiumUser->podiumName]) . ($subscriptionCount ? ' ' . Html::tag('span', $subscriptionCount, ['class' => 'badge']) : ''), 
        'url'   => ['profile/index'],
        'items' => [
            ['label' => Yii::t('podium/view', 'My Profile'), 'url' => ['profile/index']],
            ['label' => Yii::t('podium/view', 'Account Details'), 'url' => ['profile/details']],
            ['label' => Yii::t('podium/view', 'Forum Details'), 'url' => ['profile/forum']],
            ['label' => Yii::t('podium/view', 'Subscriptions'), 'url' => ['profile/subscriptions']],
        ]
    ];
    $items[] = [
        'label' => Yii::t('podium/view', 'Messages') . ($messageCount ? ' ' . Html::tag('span', $messageCount, ['class' => 'badge']) : ''), 
        'url'   => ['messages/inbox'],
        'items' => [
            ['label' => Yii::t('podium/view', 'Inbox'), 'url' => ['messages/inbox']],
            ['label' => Yii::t('podium/view', 'Sent'), 'url' => ['messages/sent']],
            ['label' => Yii::t('podium/view', 'New Message'), 'url' => ['messages/new']],
        ]
    ];
    if ($podiumModule->userComponent == Podium::USER_OWN) {
        $items[] = ['label' => Yii::t('podium/view', 'Sign out'), 'url' => ['profile/logout'], 'linkOptions' => ['data-method' => 'post']];
    }
}

NavBar::begin([
    'brandLabel'            => $podiumModule->config->get('name'),
    'brandUrl'              => ['default/index'],
    'options'               => ['class' => 'navbar-inverse navbar-default', 'id' => 'top'],
    'innerContainerOptions' => ['class' => 'container-fluid',]
]);
echo Nav::widget([
    'options'         => ['class' => 'navbar-nav navbar-right'],
    'encodeLabels'    => false,
    'activateParents' => true,
    'items'           => $items,
]);
NavBar::end();
