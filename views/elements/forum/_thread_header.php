<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */

use yii\helpers\Url;

?>
<?php if (isset($category, $forum, $slug)): ?>
<tr>
    <td colspan="4" class="small">
        <ul class="list-inline">
            <li class="text-muted"><?= Yii::t('podium/view', 'Show only') ?></li>
<?php if (!Yii::$app->user->isGuest): ?>
            <li><a href="<?= Url::to(['default/forum', 'cid' => $category, 'id' => $forum, 'slug' => $slug, 'toggle' => 'new']) ?>" class="btn btn-success btn-xs <?= !empty($filters['new']) && $filters['new'] ? 'active' : '' ?>"><span class="glyphicon glyphicon-leaf"></span> <?= Yii::t('podium/view', 'New Posts') ?></a></li>
            <li><a href="<?= Url::to(['default/forum', 'cid' => $category, 'id' => $forum, 'slug' => $slug, 'toggle' => 'edit']) ?>" class="btn btn-warning btn-xs <?= !empty($filters['edit']) && $filters['edit'] ? 'active' : '' ?>"><span class="glyphicon glyphicon-comment"></span> <?= Yii::t('podium/view', 'Edited Posts') ?></a></li>
<?php endif; ?>
            <li><a href="<?= Url::to(['default/forum', 'cid' => $category, 'id' => $forum, 'slug' => $slug, 'toggle' => 'hot']) ?>" class="btn btn-default btn-xs <?= !empty($filters['hot']) && $filters['hot'] ? 'active' : '' ?>"><span class="glyphicon glyphicon-fire"></span> <?= Yii::t('podium/view', 'Hot Threads') ?></a></li>
            <li><a href="<?= Url::to(['default/forum', 'cid' => $category, 'id' => $forum, 'slug' => $slug, 'toggle' => 'pin']) ?>" class="btn btn-default btn-xs <?= !empty($filters['pin']) && $filters['pin'] ? 'active' : '' ?>"><span class="glyphicon glyphicon-pushpin"></span> <?= Yii::t('podium/view', 'Pinned Threads') ?></a></li>
            <li><a href="<?= Url::to(['default/forum', 'cid' => $category, 'id' => $forum, 'slug' => $slug, 'toggle' => 'lock']) ?>" class="btn btn-default btn-xs <?= !empty($filters['lock']) && $filters['lock'] ? 'active' : '' ?>"><span class="glyphicon glyphicon-lock"></span> <?= Yii::t('podium/view', 'Locked Threads') ?></a></li>
            <li><a href="<?= Url::to(['default/forum', 'cid' => $category, 'id' => $forum, 'slug' => $slug, 'toggle' => 'all']) ?>" class="btn btn-info btn-xs"><span class="glyphicon glyphicon-asterisk"></span> <?= Yii::t('podium/view', 'All Threads') ?></a></li>
        </ul>
    </td>
</tr>
<?php endif; ?>
<tr>
    <th class="col-sm-7"><small><?= Yii::t('podium/view', 'Thread') ?></small></th>
    <th class="col-sm-1 text-center"><small><?= Yii::t('podium/view', 'Replies') ?></small></th>
    <th class="col-sm-1 text-center"><small><?= Yii::t('podium/view', 'Views') ?></small></th>
    <th class="col-sm-3"><small><?= Yii::t('podium/view', 'Latest Post') ?></small></th>
</tr>
