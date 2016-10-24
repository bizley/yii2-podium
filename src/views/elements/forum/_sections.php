<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

?>
<div class="panel-group" role="tablist" aria-multiselectable="true">
    <?= \yii\widgets\ListView::widget([
        'dataProvider' => $dataProvider,
        'itemView'     => '/elements/forum/_section',
        'separator'    => "\n<br>\n",
        'summary'      => '',
        'emptyText'    => '<h3>' . Yii::t('podium/view', 'No categories have been added yet.') . '</h3>',
    ]); ?>
</div>
