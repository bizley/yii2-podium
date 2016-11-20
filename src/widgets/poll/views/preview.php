<?php

use yii\helpers\Html;

?>
<hr>
<p><strong><?= Yii::t('podium/view', 'Poll'); ?>: <?= Html::encode($model->poll_question) ?></strong></p>
<ul class="list-inline">
    <li><small><?= Yii::t('podium/view', 'Number of votes'); ?>: <span class="badge"><?= Html::encode($model->poll_votes) ?></span></small></li>
<?php if ($model->poll_hidden): ?>
    <li><small><span class="glyphicon glyphicon-eye-close"></span> <?= Yii::t('podium/view', 'Results hidden before voting'); ?></small></li>
<?php else: ?>
    <li><small><span class="glyphicon glyphicon-eye-open"></span> <?= Yii::t('podium/view', 'Results visible before voting'); ?></small></li>
<?php endif; ?>
    <li><small>
        <span class="glyphicon glyphicon-calendar"></span> 
        <?= Yii::t('podium/view', 'Poll ends at'); ?>: 
        <?= empty($model->poll_end) ? '-' : Html::encode($model->poll_end) . ' 23:59' ?>
    </small></li>
</ul>
<ul>
<?php foreach ($model->poll_answers as $answer): ?>
<?php if (!empty($answer)): ?>
    <li><?= Html::encode($answer) ?></li>
<?php endif; ?>
<?php endforeach; ?>
</ul>