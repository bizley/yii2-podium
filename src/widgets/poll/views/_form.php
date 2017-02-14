<?php

use kartik\date\DatePicker;

$answers = 0;
$options = [];
for ($a = 1; $a <= 10; $a++) {
    $opts = ['placeholder' => Yii::t('podium/view', 'Leave empty to remove')];
    $opts['id'] = 'thread-poll_answers' . ($a > 1 ? '_' . $a : '');
    if (!empty($model->$pollAnswers[$a - 1])) {
        $opts['value'] = $model->$pollAnswers[$a - 1];
        $answers++;
    } else {
        $opts['value'] = null;
    }
    $options[$a] = $opts;
}
$answers = max([2, $answers]);
$this->registerJs(<<<JS
var answers = $answers; $(".podium-poll-plus button").click(function(e) { e.preventDefault(); answers++; if ($(".podium-poll-opt-" + answers).length) { $(".podium-poll-opt-" + answers).removeClass("hide"); } if (answers >= 10) { $(".podium-poll-plus").addClass("hide"); }});
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

?>
<div class="row">
    <?= $form->field($model, $pollQuestion, $fieldLayoutLong); ?>
</div>
<div class="row">
    <?= $form->field($model, $pollVotes, $fieldLayoutShort); ?>
</div>
<div class="row">
    <?= $form->field($model, $pollHidden, [
        'checkboxTemplate' => "<div class=\"col-sm-offset-3 col-sm-9\">\n{beginLabel}\n{input}\n{labelTitle}\n{endLabel}\n{error}\n{hint}\n</div>"
    ])->checkbox(); ?>
</div>
<div class="row">
    <?= $form->field($model, $pollEnd, $fieldLayoutShort)->widget(DatePicker::classname(), [
        'removeButton' => false, 'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy-mm-dd']
    ]); ?>
</div>
<?php foreach ($options as $index => $option): ?>
<div class="row <?= $index > 2 ? 'podium-poll-opt-' . $index : '' ?> <?= $option['value'] === null && $index > 2 ? 'hide' : '' ?>">
    <?= $form->field($model, $pollAnswers .'[]', $fieldLayoutLong)
            ->label(Yii::t('podium/view', 'Option #{n}', ['n' => $index]), ['for' => $option['id']])
            ->textInput($option); ?>
</div>
<?php endforeach; ?>
<div class="row podium-poll-plus">
    <div class="col-sm-offset-3 col-sm-9">
        <button class="btn btn-default btn-xs"><span class="glyphicon glyphicon-plus"></span> <?= Yii::t('podium/view', 'One more'); ?></button>
    </div>
</div>