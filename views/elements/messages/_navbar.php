<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
use yii\helpers\Html;

echo Html::beginTag('ul', ['class' => 'nav nav-tabs']);
echo Html::tag('li', Html::a('<span class="glyphicon glyphicon-inbox"></span> ' . Yii::t('podium/view', 'Messages Inbox'), ['inbox']), ['role' => 'presentation', 'class' => $active == 'inbox' ? 'active' : '']);
echo Html::tag('li', Html::a('<span class="glyphicon glyphicon-upload"></span> ' . Yii::t('podium/view', 'Sent Messages'), ['sent']), ['role' => 'presentation', 'class' => $active == 'sent' ? 'active' : '']);
echo Html::tag('li', Html::a('<span class="glyphicon glyphicon-trash"></span> ' . Yii::t('podium/view', 'Deleted Messages'), ['deleted']), ['role' => 'presentation', 'class' => $active == 'trash' ? 'active' : '']);
echo Html::tag('li', Html::a('<span class="glyphicon glyphicon-envelope"></span> ' . Yii::t('podium/view', 'New Message'), ['new']), ['role' => 'presentation', 'class' => $active == 'new' ? 'active' : '']);
if ($active == 'view') {
    echo Html::tag('li', Html::a('<span class="glyphicon glyphicon-eye-open"></span> ' . Yii::t('podium/view', 'View Message'), '#'), ['role' => 'presentation', 'class' => 'active']);
}
echo Html::endTag('ul');