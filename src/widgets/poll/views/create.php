<?php

use kartik\date\DatePicker;
use yii\helpers\Html;

$this->registerJs(<<<JS
$(".podium-poll-add").click(function(e) {
    e.preventDefault();
    $(".new-poll").removeClass("hide");
    $("#poll_added").val(1);
    $(this).addClass("hide");
});
$(".podium-poll-discard").click(function(e) {
    e.preventDefault();
    $(".new-poll").addClass("hide");
    $("#poll_added").val(0);
    $(".podium-poll-add").removeClass("hide");
});
JS
);

$fieldLayoutLong = [
    'labelOptions' => ['class' => 'control-label col-sm-3'], 
    'template' => "{label}\n<div class=\"col-sm-9\">{input}\n{hint}\n{error}</div>"
];
$fieldLayoutShort = [
    'labelOptions' => ['class' => 'control-label col-sm-3'], 
    'template' => "{label}\n<div class=\"col-sm-3\">{input}\n{hint}\n{error}</div>"
];

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
            <div class="row">
                <?= $form->field($model, 'pollQuestion', $fieldLayoutLong); ?>
            </div>
            <div class="row">
                <?= $form->field($model, 'pollVotes', $fieldLayoutShort); ?>
            </div>
            <div class="row">
                <?= $form->field($model, 'pollHidden', [
                    'checkboxTemplate' => "<div class=\"col-sm-offset-3 col-sm-9\">\n{beginLabel}\n{input}\n{labelTitle}\n{endLabel}\n{error}\n{hint}\n</div>"
                ])->checkbox(); ?>
            </div>
            <div class="row">
                <?= $form->field($model, 'pollEnd', $fieldLayoutShort)->widget(DatePicker::classname(), [
                    'removeButton' => false, 'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy-mm-dd']
                ]); ?>
            </div>
<?php $opts = ['placeholder' => Yii::t('podium/view', 'Leave empty to remove')]; ?>
<?php $answers = 0; for ($a = 1; $a <= 10; $a++):
    $opts['id'] = 'thread-poll_answers' . ($a > 1 ? '_' . $a : '');
    if (!empty($model->pollAnswers[$a - 1])):
        $opts['value'] = $model->pollAnswers[$a - 1];
        $answers++;
    else:
        $opts['value'] = null;
    endif; ?>
            <div class="row <?= $a > 2 ? 'podium-poll-opt-' . $a : '' ?> <?= $opts['value'] === null && $a > 2 ? 'hide' : '' ?>">
                <?= $form->field($model, 'pollAnswers[]', $fieldLayoutLong)
                        ->label(Yii::t('podium/view', 'Option #{n}', ['n' => $a]), ['for' => $opts['id']])
                        ->textInput($opts); ?>
            </div>
<?php endfor; ?>
            <div class="row podium-poll-plus">
                <div class="col-sm-offset-3 col-sm-9">
                    <button class="btn btn-default btn-xs"><span class="glyphicon glyphicon-plus"></span> <?= Yii::t('podium/view', 'One more'); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$answers = max([2, $answers]);
$this->registerJs(<<<JS
var answers = $answers;
$(".podium-poll-plus button").click(function(e) {
    e.preventDefault();
    answers++;
    if ($(".podium-poll-opt-" + answers).length) {
        $(".podium-poll-opt-" + answers).removeClass("hide");
    }
    if (answers >= 10) {
        $(".podium-poll-plus").addClass("hide");
    }
});
JS
);
