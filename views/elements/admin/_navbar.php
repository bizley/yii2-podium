<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */

use yii\helpers\Url;

?>
<ul class="nav nav-tabs">
    <li role="presentation" class="<?= $active == 'index' ? 'active' : '' ?>"><a href="<?= Url::to(['admin/index']) ?>"><span class="glyphicon glyphicon-blackboard"></span> <?= Yii::t('podium/view', 'Dashboard') ?></a></li>
    <li role="presentation" class="<?= $active == 'categories' ? 'active' : '' ?>"><a href="<?= Url::to(['admin/categories']) ?>"><span class="glyphicon glyphicon-bullhorn"></span> <?= Yii::t('podium/view', 'Forums') ?></a></li>
    <li role="presentation" class="<?= $active == 'members' ? 'active' : '' ?>"><a href="<?= Url::to(['admin/members']) ?>"><span class="glyphicon glyphicon-user"></span> <?= Yii::t('podium/view', 'Members') ?></a></li>
    <li role="presentation" class="<?= $active == 'mods' ? 'active' : '' ?>"><a href="<?= Url::to(['admin/mods']) ?>"><span class="glyphicon glyphicon-scissors"></span> <?= Yii::t('podium/view', 'Moderators') ?></a></li>
    <li role="presentation" class="<?= $active == 'contents' ? 'active' : '' ?>"><a href="<?= Url::to(['admin/contents']) ?>"><span class="glyphicon glyphicon-text-color"></span> <?= Yii::t('podium/view', 'Contents') ?></a></li>
    <li role="presentation" class="<?= $active == 'settings' ? 'active' : '' ?>"><a href="<?= Url::to(['admin/settings']) ?>"><span class="glyphicon glyphicon-cog"></span> <?= Yii::t('podium/view', 'Settings') ?></a></li>
    <li role="presentation" class="<?= $active == 'logs' ? 'active' : '' ?>"><a href="<?= Url::to(['admin/logs']) ?>"><span class="glyphicon glyphicon-filter"></span> <?= Yii::t('podium/view', 'Logs') ?></a></li>
</ul>