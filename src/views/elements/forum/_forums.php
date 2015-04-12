<table class="table table-hover">
    <?= $this->render('/elements/forum/_forum_header') ?>
    <?= $this->render('/elements/forum/_forum_list', ['category' => $category]) ?>
</table>