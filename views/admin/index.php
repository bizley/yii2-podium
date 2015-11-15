<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
use yii\helpers\Url;

$this->title                   = Yii::t('podium/view', 'Administration Dashboard');
$this->params['breadcrumbs'][] = $this->title;

echo  $this->render('/elements/admin/_navbar', ['active' => 'index']); ?>

<br>
<div class="row">
    <div class="col-sm-3">
        <div class="panel panel-success">
            <div class="panel-heading"><?= Yii::t('podium/view', 'Newest members') ?></div>
            <table class="table">
<?php foreach ($members as $member): ?>
                <tr>
                    <td>
                        <a href="<?= Url::to(['admin/view', 'id' => $member->id]) ?>"><?= $member->getPodiumName() ?></a>
                        <?= Yii::$app->formatter->asRelativeTime($member->created_at) ?>
                    </td>
                </tr>
<?php endforeach; ?>
            </table>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="panel panel-info">
            <div class="panel-heading">Panel heading without title</div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="panel panel-warning">
            <div class="panel-heading">Panel heading without title</div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="panel panel-danger">
            <div class="panel-heading">Panel heading without title</div>
        </div>
    </div>
</div>