<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

use bizley\podium\helpers\Helper;
use bizley\podium\models\Activity;

$lastActive = Activity::lastActive();

?>
<div class="panel panel-default">
    <div class="panel-body small">
        <p>
            <?= Yii::t('podium/view', '{n, plural, =1{# active user} other{# active users}} (in the past 15 minutes)', ['n' => !empty($lastActive['count']) ? $lastActive['count'] : 0]) ?><br>
            <?= Yii::t('podium/view', '{n, plural, =1{# member} other{# members}}', ['n' => !empty($lastActive['members']) ? $lastActive['members'] : 0]) ?>,
            <?= Yii::t('podium/view', '{n, plural, =1{# guest} other{# guests}}', ['n' => !empty($lastActive['guests']) ? $lastActive['guests'] : 0]) ?>,
            <?= Yii::t('podium/view', '{n, plural, =1{# anonymous user} other{# anonymous users}}', ['n' => !empty($lastActive['anonymous']) ? $lastActive['anonymous'] : 0]) ?>
        </p>
<?php if (!empty($lastActive['names'])): ?>
        <p>
<?php foreach ($lastActive['names'] as $id => $name): ?>
            <?= Helper::podiumUserTag($name['name'], $name['role'], $id, $name['slug']) ?>
<?php endforeach; ?>
        </p>
<?php endif; ?>
    </div>
    <div class="panel-footer small">
        <ul class="list-inline">
            <li><?= Yii::t('podium/view', 'Members') ?> <span class="badge"><?= Activity::totalMembers() ?></span></li>
            <li><?= Yii::t('podium/view', 'Threads') ?> <span class="badge"><?= Activity::totalThreads() ?></span></li>
            <li><?= Yii::t('podium/view', 'Posts') ?> <span class="badge"><?= Activity::totalPosts() ?></span></li>
        </ul>
    </div>
</div>
