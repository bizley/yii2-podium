<?php

use yii\widgets\ListView;

echo ListView::widget([
    'dataProvider' => $dataProvider,
    'itemView' => '/elements/search/_thread',
    'summary' => '',
    'emptyText' => Yii::t('podium/view', 'No matching posts can be found.'),
    'emptyTextOptions' => ['tag' => 'td', 'class' => 'text-muted', 'colspan' => 4],
    'options' => ['tag' => 'tbody'],
    'itemOptions' => ['tag' => 'tr', 'class' => 'podium-thread-line']
]);