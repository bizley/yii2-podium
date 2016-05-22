<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */

?>
<table class="table table-hover">
    <?= $this->render('/elements/search/_thread_header') ?>
    <?= $this->render('/elements/search/_thread_list', ['dataProvider' => $dataProvider, 'type' => $type]) ?>
</table>
