<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */

use yii\helpers\Html;

$title = 'Search for {type}';
if (!empty($query)) {
    $title .= ' with "{query}"';
}
if (!empty($author)) {
    $title .= ' by "{author}"';
}

?>
<div class="row">
    <div class="col-sm-12">
        <h4>
            <?= Yii::t('podium/view', $title, ['query' => Html::encode($query), 'author' => Html::encode($author), 'type' => $type == 'topics' ? 'threads' : 'posts']) ?>
        </h4>
    </div>
</div>
<?= $this->render('/elements/search/_threads_search_posts', ['dataProvider' => $dataProvider, 'type' => $type, 'query' => Html::encode($query)]) ?>
