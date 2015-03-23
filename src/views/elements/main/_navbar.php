<?php
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;

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
    $items[] = ['label' => Yii::t('podium/layout', 'Profile'), 'url' => ['account/profile']];
    $items[] = ['label' => Yii::t('podium/layout', 'Logout ({USER})', ['USER' => Yii::$app->user->identity->username]), 'url' => ['account/logout'], 'linkOptions' => ['data-method' => 'post']];
}

NavBar::begin([
    'brandLabel'            => 'Podium',
    'brandUrl'              => ['default/index'],
    'options'               => ['class' => 'navbar-inverse navbar-default',],
    'innerContainerOptions' => ['class' => 'container-fluid',]
]);
echo Nav::widget([
    'options' => ['class' => 'navbar-nav navbar-right'],
    'items'   => $items,
]);
NavBar::end();