<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */

use yii\helpers\Url;

?>
<div class="panel panel-default">
    <div class="panel-heading">
<?php if (!Yii::$app->user->isGuest): ?>
        <a href="<?= Url::to(['default/unread-posts']) ?>" class="btn btn-info btn-xs pull-right"><span class="glyphicon glyphicon-flash"></span> <?= Yii::t('podium/view', 'Unread posts') ?></a>
<?php endif ?>
        <?= Yii::t('podium/view', 'Latest posts') ?>
    </div>
<?php if ($latest): ?>
    <table class="table table-hover">
<?php foreach ($latest as $post): ?>
        <tr>
            <td>
                <a href="<?= Url::to(['default/show', 'id' => $post['id']]) ?>" class="center-block"><?= $post['title'] ?></a>
                <small>
                    <?= Yii::$app->formatter->asRelativeTime($post['created']) ?> 
                    <?= $post['author'] ?>
                </small>
            </td>
        </tr>
<?php endforeach; ?>
    </table>
<?php else: ?>
    <div class="panel-body">
        <small><?= Yii::t('podium/view', 'No posts have been added yet.') ?></small>
    </div>
<?php endif; ?>
</div>
