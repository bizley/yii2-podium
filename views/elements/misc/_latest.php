<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= Yii::t('podium/view', 'Latest posts') ?>
    </div>
<?php if ($latest): ?>
    <table class="table table-hover">
<?php foreach ($latest as $post): ?>
        <tr>
            <td>
                <a href="<?= \yii\helpers\Url::to(['default/show', 'id' => $post['id']]) ?>" class="center-block"><?= $post['title'] ?></a>
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
        <small><?= Yii::t('podium/view', 'No posts have been added yet') ?></small>
    </div>
<?php endif; ?>
</div>