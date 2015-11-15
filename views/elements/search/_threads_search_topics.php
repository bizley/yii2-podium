<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
?>
<table class="table table-hover">
    <?= $this->render('/elements/search/_thread_header') ?>
    <?= $this->render('/elements/search/_thread_list', ['dataProvider' => $dataProvider, 'type' => $type]) ?>
</table>