<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

use yii\bootstrap\NavBar;

?>
<?php NavBar::begin([
    'brandLabel'            => 'Podium',
    'brandUrl'              => ['forum/index'],
    'options'               => ['class' => 'navbar-inverse navbar-default',],
    'innerContainerOptions' => ['class' => 'container-fluid',]
]);
NavBar::end();
