<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

use yii\widgets\ListView;
use yii\widgets\Pjax;

$words = [];
$query = preg_replace('/\s+/', ' ', trim($query));
$tmp = explode(' ', $query);
foreach ($tmp as $tmp) {
    if (mb_strlen($tmp, 'UTF-8') > 2) {
        $words[] = $tmp;
    }
}

?>
<br>
<?php Pjax::begin();
echo ListView::widget([
    'dataProvider' => $dataProvider,
    'itemView' => '/elements/search/_post',
    'viewParams' => ['words' => $words, 'type' => $type],
    'summary' => '',
    'emptyText' => $type == 'topics' ? Yii::t('podium/view', 'No matching threads can be found.') : Yii::t('podium/view', 'No matching posts can be found.'),
    'emptyTextOptions' => ['tag' => 'h3', 'class' => 'text-muted'],
    'pager' => ['options' => ['class' => 'pagination pull-right']]
]);
Pjax::end(); ?>
<br>
