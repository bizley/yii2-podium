<?php

use yii\helpers\Html;

$this->registerJs(<<<JS
$(".podium-poll-add").click(function(e) { e.preventDefault(); $(".new-poll").removeClass("hide"); $("#poll_added").val(1); $(this).addClass("hide"); });
$(".podium-poll-discard").click(function(e) { e.preventDefault(); $(".new-poll").addClass("hide"); $("#poll_added").val(0); $(".podium-poll-add").removeClass("hide"); });
JS
);

echo Html::activeHiddenInput($model, 'pollAdded', ['id' => 'poll_added']);
?>
<button class="btn btn-success podium-poll-add <?= $model->pollAdded ? 'hide' : '' ?>">
    <span class="glyphicon glyphicon-tasks"></span> <?= Yii::t('podium/view', 'Add poll to this thread'); ?>
</button>

<div class="new-poll <?= $model->pollAdded ? '' : 'hide' ?>">
    <div class="panel panel-default">
        <div class="panel-heading">
            <button class="btn btn-xs btn-danger pull-right podium-poll-discard"><span class="glyphicon glyphicon-remove"></span> <?= Yii::t('podium/view', 'Discard poll'); ?></button>
            <strong><?= Yii::t('podium/view', 'New poll'); ?></strong>
        </div>
        <div class="panel-body">
            <?= $this->render('_form', [
                'form' => $form,
                'model' => $model,
                'pollQuestion' => 'pollQuestion',
                'pollVotes' => 'pollVotes',
                'pollHidden' => 'pollHidden',
                'pollEnd' => 'pollEnd',
                'pollAnswers' => 'pollAnswers',
            ]) ?>
        </div>
    </div>
</div>
