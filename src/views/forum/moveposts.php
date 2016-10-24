<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

use yii\helpers\Html;
use yii\widgets\ListView;
use yii\widgets\Pjax;

$this->title = Yii::t('podium/view', 'Move Posts');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Main Forum'), 'url' => ['forum/index']];
$this->params['breadcrumbs'][] = ['label' => $model->forum->category->name, 'url' => ['forum/category', 'id' => $model->forum->category->id, 'slug' => $model->forum->category->slug]];
$this->params['breadcrumbs'][] = ['label' => $model->forum->name, 'url' => ['forum/forum', 'cid' => $model->forum->category->id, 'id' => $model->forum->id, 'slug' => $model->forum->slug]];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['forum/thread', 'cid' => $model->forum->category->id, 'fid' => $model->forum->id, 'id' => $model->id, 'slug' => $model->slug]];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-warning">
            <div class="panel-heading">
                <strong><?= Yii::t('podium/view', 'Select posts to move') ?></strong>:
            </div>
        </div>
    </div>
</div><br>

<?= Html::beginForm(); ?>
<?php Pjax::begin(); ?>
<?= ListView::widget([
    'dataProvider'     => $dataProvider,
    'itemView'         => '/elements/forum/_post_select',
    'summary'          => '',
    'emptyText'        => Yii::t('podium/view', 'No posts have been added yet.'),
    'emptyTextOptions' => ['tag' => 'h3', 'class' => 'text-muted'],
    'pager'            => ['options' => ['class' => 'pagination pull-right']]
]);  ?>
<?php Pjax::end(); ?>
    <br>
    <div class="row">
        <div class="col-sm-10 col-sm-offset-2">
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <?= Html::label(Yii::t('podium/view', 'Select a thread for this posts to be moved to'), 'newthread') ?>
                                <p>* <?= Yii::t('podium/view', 'Forums you can moderate are marked with asterisk.') ?></p>
                                <?= Html::dropDownList('newthread', null, $list, ['id' => 'newthread', 'class' => 'form-control', 'options' => $options, 'encode' => false]) ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <?= Html::label(Yii::t('podium/view', 'Name of the new thread'), 'newname') ?>
                                <?= Html::textInput('newname', null, ['id' => 'newname', 'class' => 'form-control']) ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <?= Html::label(Yii::t('podium/view', 'Parent forum of the new thread'), 'newforum') ?>
                                <p>* <?= Yii::t('podium/view', 'Forums you can moderate are marked with asterisk.') ?></p>
                                <?= Html::dropDownList('newforum', null, $listforum, ['id' => 'newforum', 'class' => 'form-control', 'encode' => false]) ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-sm-12">
                            <?= Html::submitButton('<span class="glyphicon glyphicon-ok-sign"></span> ' . Yii::t('podium/view', 'Move Posts'), ['class' => 'btn btn-block btn-primary', 'name' => 'save-button']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><br>
<?= Html::endForm();
