<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
use bizley\podium\models\Thread;
use yii\widgets\ListView;

echo ListView::widget([
    'dataProvider' => (new Thread)->search($forum),
    'itemView' => '/elements/forum/_thread',
    'summary' => '',
    'emptyText' => Yii::t('podium/view', 'No threads have been added yet.'),
    'emptyTextOptions' => ['tag' => 'td', 'class' => 'text-muted', 'colspan' => 4],
    'options' => ['tag' => 'tbody'],
    'itemOptions' => ['tag' => 'tr', 'class' => 'podium-thread-line']
]);