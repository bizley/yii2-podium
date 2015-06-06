<?php

use yii\widgets\ListView;

echo ListView::widget([
    'dataProvider' => $dataProvider,
    'itemView' => '/elements/search/_thread_' . $type,
    'summary' => '',
    'emptyText' => Yii::t('podium/view', $type == 'topics' ? 'No matching threads can be found.' : 'No matching posts can be found.'),
    'emptyTextOptions' => ['tag' => 'td', 'class' => 'text-muted', 'colspan' => 4],
    'options' => ['tag' => 'tbody'],
    'itemOptions' => ['tag' => 'tr', 'class' => 'podium-thread-line']
]);