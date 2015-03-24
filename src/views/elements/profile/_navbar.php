<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>
<ul class="nav nav-pills nav-stacked">
    <?= Html::tag('li', Html::a(Yii::t('podium/view', 'My Profile'), ['index']), ['role' => 'presentation', 'class' => $active == 'profile' ? 'active' : null]) ?>
    <?= Html::tag('li', Html::a(Yii::t('podium/view', 'Account Details'), ['details']), ['role' => 'presentation', 'class' => $active == 'details' ? 'active' : null]) ?>
    <?= Html::tag('li', Html::a(Yii::t('podium/view', 'Forum Details'), ['forum']), ['role' => 'presentation', 'class' => $active == 'forum' ? 'active' : null]) ?>
    <?= Html::tag('li', Html::a(Html::tag('span', '0/0', ['class' => 'badge pull-right']) . Yii::t('podium/view', 'Messages'), ['messages']), ['role' => 'presentation', 'class' => $active == 'messages' ? 'active' : null]) ?>
    <?= Html::tag('li', Html::a(Html::tag('span', '0/0', ['class' => 'badge pull-right']) . Yii::t('podium/view', 'Subscriptions'), ['subscriptions']), ['role' => 'presentation', 'class' => $active == 'subscriptions' ? 'active' : null]) ?>
</ul>