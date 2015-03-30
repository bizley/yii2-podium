<?php
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\Html;

$items = [
    ['label' => Yii::t('podium/layout', 'Home'), 'url' => ['default/index']],
//    ['label' => Yii::t('podium/layout', 'Contact'), 'url' => ['/podium/contact']],
];
if (Yii::$app->user->isGuest) {
    $items[] = ['label' => Yii::t('podium/layout', 'Login'), 'url' => ['account/login']];
    $items[] = ['label' => Yii::t('podium/layout', 'Register'), 'url' => ['account/register']];
}
else {
    if (Yii::$app->user->can('settings')) {
        $items[] = ['label' => Yii::t('podium/layout', 'Administration'), 'url' => ['admin/index']];
    }
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
        'label' => Yii::t('podium/layout', 'Messages') . ' ' . Html::tag('span', '0', ['class' => 'badge']), 
        'url' => ['messages/inbox'],
        'items' => [
            ['label' => Yii::t('podium/view', 'Inbox'), 'url' => ['messages/inbox']],
            ['label' => Yii::t('podium/view', 'Sent'), 'url' => ['messages/sent']],
            ['label' => Yii::t('podium/view', 'Deleted'), 'url' => ['messages/deleted']],
            ['label' => Yii::t('podium/view', 'New Message'), 'url' => ['messages/new']],
        ]
    ];
    $items[] = ['label' => Yii::t('podium/layout', 'Logout ({USER})', ['USER' => Yii::$app->user->identity->username]), 'url' => ['profile/logout'], 'linkOptions' => ['data-method' => 'post']];
}

NavBar::begin([
    'brandLabel'            => 'Podium',
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