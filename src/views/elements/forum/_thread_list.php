<?php

use bizley\podium\models\Thread;
use yii\widgets\ListView;

echo ListView::widget([
    'dataProvider' => (new Thread)->search($forum),
    'itemView' => '/elements/forum/_thread',
    'summary' => '',
    'emptyText' => Yii::t('podium/view', 'No threads have been added yet.'),
    'emptyTextOptions' => ['tag' => 'td', 'class' => 'text-muted', 'colspan' => 5],
    'options' => ['tag' => 'tr', 'class' => null],
    'itemOptions' => ['tag' => false]
]);