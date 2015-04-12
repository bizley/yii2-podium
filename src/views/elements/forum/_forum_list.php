<?php

use bizley\podium\models\Forum;
use yii\widgets\ListView;

echo ListView::widget([
    'dataProvider' => (new Forum)->search($category),
    'itemView' => '/elements/forum/_forum',
    'summary' => '',
    'emptyText' => Yii::t('podium/view', 'No forums have been added yet.'),
    'emptyTextOptions' => ['tag' => 'td', 'class' => 'text-muted', 'colspan' => 4],
    'options' => ['tag' => 'tr', 'class' => null],
    'itemOptions' => ['tag' => false]
]); /*?>

<tr>
    <td><a href="" class="center-block">Nazwa forum</a><small>forum subtitle</small></td>
    <td class="text-right">65</td>
    <td class="text-right">1245</td>
    <td><a href="" class="center-block">Tytuł najnowszego posta</a><small>Apr 14, 2015 <a href="" class="btn btn-default btn-xs">Bizley</a></small></td>
</tr>*/