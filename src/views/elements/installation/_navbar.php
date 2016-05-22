<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */

use yii\bootstrap\NavBar;

?>
<?php NavBar::begin([
    'brandLabel'            => 'Podium',
    'brandUrl'              => ['default/index'],
    'options'               => ['class' => 'navbar-inverse navbar-default',],
    'innerContainerOptions' => ['class' => 'container-fluid',]
]);
NavBar::end();
