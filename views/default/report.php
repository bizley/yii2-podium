<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */

use bizley\quill\Quill;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('podium/view', 'Report Post');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Main Forum'), 'url' => ['default/index']];
$this->params['breadcrumbs'][] = ['label' => Html::encode($category->name), 'url' => ['default/category', 'id' => $category->id, 'slug' => $category->slug]];
$this->params['breadcrumbs'][] = ['label' => Html::encode($forum->name), 'url' => ['default/forum', 'cid' => $forum->category_id, 'id' => $forum->id, 'slug' => $forum->slug]];
$this->params['breadcrumbs'][] = ['label' => Html::encode($thread->name), 'url' => ['default/thread', 'cid' => $thread->category_id, 'fid' => $thread->forum_id, 'id' => $thread->id, 'slug' => $thread->slug]];
$this->params['breadcrumbs'][] = $this->title;

?>
<?= $this->render('/elements/forum/_post', ['model' => $post, 'category' => $category->id, 'slug' => $thread->slug]) ?>
<br>
<div class="row">
    <div class="col-sm-10 col-sm-offset-2">
        <div class="panel panel-default">
            <?php $form = ActiveForm::begin(['id' => 'report-post-form']); ?>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <?= $form->field($model, 'content')->label(Yii::t('podium/view', 'Complaint'))->widget(Quill::className(), ['options' => ['style' => 'height:500px']]) ?>
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-sm-12">
                            <?= Html::submitButton('<span class="glyphicon glyphicon-ok-sign"></span> ' . Yii::t('podium/view', 'Report post'), ['class' => 'btn btn-block btn-danger', 'name' => 'save-button']) ?>
                        </div>
                    </div>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div><br>
