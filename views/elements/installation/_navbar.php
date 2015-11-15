<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
use yii\bootstrap\NavBar;

NavBar::begin([
    'brandLabel'            => 'Podium',
    'brandUrl'              => ['default/index'],
    'options'               => ['class' => 'navbar-inverse navbar-default',],
    'innerContainerOptions' => ['class' => 'container-fluid',]
]);
NavBar::end();