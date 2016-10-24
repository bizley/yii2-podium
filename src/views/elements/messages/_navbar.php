<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

use yii\helpers\Url;

?>
<ul class="nav nav-tabs">
    <li role="presentation" class="<?= $active == 'inbox' ? 'active' : '' ?>"><a href="<?= Url::to(['messages/inbox']) ?>"><span class="glyphicon glyphicon-inbox"></span> <?= Yii::t('podium/view', 'Messages Inbox') ?></a></li>
    <li role="presentation" class="<?= $active == 'sent' ? 'active' : '' ?>"><a href="<?= Url::to(['messages/sent']) ?>"><span class="glyphicon glyphicon-upload"></span> <?= Yii::t('podium/view', 'Sent Messages') ?></a></li>
    <li role="presentation" class="<?= $active == 'new' ? 'active' : '' ?>"><a href="<?= Url::to(['messages/new']) ?>"><span class="glyphicon glyphicon-envelope"></span> <?= Yii::t('podium/view', 'New Message') ?></a></li>
<?php if ($active == 'view'): ?>
    <li role="presentation" class="active"><a href="#"><span class="glyphicon glyphicon-eye-open"></span> <?= Yii::t('podium/view', 'View Message') ?></a></li>
<?php endif; ?>
</ul>
