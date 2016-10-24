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
    <li role="presentation" class="<?= $active == 'index' ? 'active' : '' ?>">
        <a href="<?= Url::to(['admin/index']) ?>">
            <span class="glyphicon glyphicon-blackboard"></span>
            <span class="hidden-xs hidden-sm"><?= Yii::t('podium/view', 'Dashboard') ?></span>
        </a>
    </li>
    <li role="presentation" class="<?= $active == 'categories' ? 'active' : '' ?>">
        <a href="<?= Url::to(['admin/categories']) ?>">
            <span class="glyphicon glyphicon-bullhorn"></span>
            <span class="hidden-xs hidden-sm"><?= Yii::t('podium/view', 'Forums') ?></span>
        </a>
    </li>
    <li role="presentation" class="<?= $active == 'members' ? 'active' : '' ?>">
        <a href="<?= Url::to(['admin/members']) ?>">
            <span class="glyphicon glyphicon-user"></span>
            <span class="hidden-xs hidden-sm"><?= Yii::t('podium/view', 'Members') ?></span>
        </a>
    </li>
    <li role="presentation" class="<?= $active == 'mods' ? 'active' : '' ?>">
        <a href="<?= Url::to(['admin/mods']) ?>">
            <span class="glyphicon glyphicon-scissors"></span>
            <span class="hidden-xs hidden-sm"><?= Yii::t('podium/view', 'Moderators') ?></span>
        </a>
    </li>
    <li role="presentation" class="<?= $active == 'contents' ? 'active' : '' ?>">
        <a href="<?= Url::to(['admin/contents']) ?>">
            <span class="glyphicon glyphicon-text-color"></span>
            <span class="hidden-xs hidden-sm"><?= Yii::t('podium/view', 'Contents') ?></span>
        </a>
    </li>
    <li role="presentation" class="<?= $active == 'settings' ? 'active' : '' ?>">
        <a href="<?= Url::to(['admin/settings']) ?>">
            <span class="glyphicon glyphicon-cog"></span>
            <span class="hidden-xs hidden-sm"><?= Yii::t('podium/view', 'Settings') ?></span>
        </a>
    </li>
    <li role="presentation" class="<?= $active == 'logs' ? 'active' : '' ?>">
        <a href="<?= Url::to(['admin/logs']) ?>">
            <span class="glyphicon glyphicon-filter"></span>
            <span class="hidden-xs hidden-sm"><?= Yii::t('podium/view', 'Logs') ?></span>
        </a>
    </li>
</ul>
