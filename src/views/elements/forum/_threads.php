<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

use yii\widgets\Pjax;

?>
<?php Pjax::begin() ?>
<table class="table table-hover">
    <?= $this->render('/elements/forum/_thread_header', ['forum' => $forum, 'category' => $category, 'slug' => $slug, 'filters' => $filters]) ?>
    <?= $this->render('/elements/forum/_thread_list', ['forum' => $forum, 'filters' => $filters]) ?>
</table>
<?php Pjax::end() ?>
