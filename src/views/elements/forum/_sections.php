<?php

use yii\widgets\ListView;

?>
<div class="panel-group" role="tablist" aria-multiselectable="true">
    <?= ListView::widget([
        'dataProvider' => $dataProvider,
        'itemView' => '/elements/forum/_section',
        'separator' => "\n<br>\n",
        'summary' => '',
        'emptyText' => '<h3>' . Yii::t('podium/view', 'No categories have been added yet.') . '</h3>',
    ]); ?>
</div>