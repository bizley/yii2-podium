<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;

$this->title = Yii::t('podium/view', 'Podium Settings');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Administration Dashboard'), 'url' => ['admin/index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<?= $this->render('/elements/admin/_navbar', ['active' => 'settings']); ?>
<br>
<div class="row">
    <div class="col-md-3 col-sm-4">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><?= Yii::t('podium/view', 'Settings Index') ?></h3>
            </div>
            <div class="list-group">
                <a href="#maintenance" class="list-group-item"><?= Yii::t('podium/view', 'Maintenance mode') ?></a>
                <a href="#meta" class="list-group-item"><?= Yii::t('podium/view', 'Meta data') ?></a>
                <a href="#register" class="list-group-item"><?= Yii::t('podium/view', 'Registration') ?></a>
                <a href="#posts" class="list-group-item"><?= Yii::t('podium/view', 'Posts') ?></a>
                <a href="#guests" class="list-group-item"><?= Yii::t('podium/view', 'Guests privileges') ?></a>
                <a href="#emails" class="list-group-item"><?= Yii::t('podium/view', 'E-mails') ?></a>
                <a href="#tokens" class="list-group-item"><?= Yii::t('podium/view', 'Tokens') ?></a>
                <a href="#db" class="list-group-item"><?= Yii::t('podium/view', 'Database') ?></a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-sm-8">
        <div class="panel panel-default">
            <?php $form = ActiveForm::begin(['id' => 'settings-form']); ?>
                <div class="panel-heading">
                    <h3 class="panel-title"><?= Yii::t('podium/view', 'Podium Settings') ?></h3>
                </div>
                <div class="panel-body">
                    <p><?= Yii::t('podium/view', 'Leave setting empty if you want to restore the default Podium value.') ?></p>
                    <h3 id="maintenance"><span class="label label-primary"><?= Yii::t('podium/view', 'Maintenance mode') ?></span></h3>
                    <div class="row">
                        <div class="col-sm-12">
                            <?= $form->field($model, 'maintenance_mode')->checkBox()->label(Yii::t('podium/view', 'Set forum to Maintenance mode'))
                                ->hint(Yii::t('podium/view', 'All users without Administrator privileges will be redirected to {maintenancePage}.', ['maintenancePage' => Html::a(Yii::t('podium/view', 'Maintenance page'), ['forum/maintenance'])])) ?>
                        </div>
                    </div>
                    <h3 id="meta"><span class="label label-primary"><?= Yii::t('podium/view', 'Meta data') ?></span></h3>
                    <div class="row">
                        <div class="col-sm-12">
                            <?= $form->field($model, 'name')->textInput()->label(Yii::t('podium/view', "Forum's Name")) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <?= $form->field($model, 'meta_keywords')->textInput()->label(Yii::t('podium/view', 'Global meta keywords')) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <?= $form->field($model, 'meta_description')->textInput()->label(Yii::t('podium/view', 'Global meta description')) ?>
                        </div>
                    </div>
                    <h3 id="register"><span class="label label-primary"><?= Yii::t('podium/view', 'Registration') ?></span></h3>
                    <div class="row">
                        <div class="col-sm-12">
                            <?= $form->field($model, 'registration_off')->checkBox()->label(Yii::t('podium/view', 'Switch registration off'))
                                ->hint(Yii::t('podium/view', 'New users registration will not be available. This setting is ignored in case of running Podium with Inherited User Identity.')) ?>
                        </div>
                        <div class="col-sm-12">
                            <?= $form->field($model, 'use_captcha')->checkBox()->label(Yii::t('podium/view', 'Add captcha in registration form')) ?>
                        </div>
                        <div class="col-sm-12">
                            <?= $form->field($model, 'recaptcha_sitekey')->textInput()->label(Yii::t('podium/view', 'Use reCAPTCHA instead of standard Captcha - Enter Site Key')) ?>
                        </div>
                        <div class="col-sm-12">
                            <?= $form->field($model, 'recaptcha_secretkey')->textInput()->label(Yii::t('podium/view', 'Use reCAPTCHA instead of standard Captcha - Enter Secret Key')) ?>
                        </div>
                        <div class="col-sm-12">
                            <?= Yii::t('podium/view', '{Click here} to register your site and get reCAPTCHA keys.', ['Click here' => Html::a(Yii::t('podium/view', 'Click here'), 'https://www.google.com/recaptcha/admin', ['target' => '_blank'])]) ?>
                        </div>
                    </div>
                    <h3 id="posts"><span class="label label-primary"><?= Yii::t('podium/view', 'Posts') ?></span></h3>
                    <div class="row">
                        <div class="col-sm-12">
                            <?= $form->field($model, 'hot_minimum')->textInput()->label(Yii::t('podium/view', 'Minimum number of posts for thread to become Hot')) ?>
                        </div>
                        <div class="col-sm-12">
                            <?= $form->field($model, 'merge_posts')->checkBox()->label(Yii::t('podium/view', 'Merge reply with post in case of the same author')) ?>
                        </div>
                        <div class="col-sm-12">
                            <?= $form->field($model, 'allow_polls')->checkBox()->label(Yii::t('podium/view', 'Allow polls adding')) ?>
                        </div>
                        <div class="col-sm-12">
                            <?= $form->field($model, 'use_wysiwyg')->checkBox()->label(Yii::t('podium/view', 'Use WYSIWYG editor'))
                                ->hint(Yii::t('podium/view', '{WYSIWYG} stands for What You See Is What You Get and Podium uses {Quill} for this way of posting. If you would like to switch it off {CodeMirror} in {Markdown} mode will be used instead.', [
                                    'WYSIWYG' => Html::a('WYSIWYG', 'https://en.wikipedia.org/wiki/WYSIWYG'),
                                    'Quill' => Html::a('Quill', 'https://quilljs.com'),
                                    'CodeMirror' => Html::a('CodeMirror', 'https://codemirror.net'),
                                    'Markdown' => Html::a('Markdown', 'https://en.wikipedia.org/wiki/Markdown')
                                ])) ?>
                        </div>
                    </div>
                    <h3 id="guests"><span class="label label-primary"><?= Yii::t('podium/view', 'Guests privileges') ?></span></h3>
                    <div class="row">
                        <div class="col-sm-12">
                            <?= $form->field($model, 'members_visible')->checkBox()->label(Yii::t('podium/view', 'Allow guests to list members')) ?>
                        </div>
                    </div>
                    <h3 id="emails"><span class="label label-primary"><?= Yii::t('podium/view', 'E-mails') ?></span></h3>
                    <div class="row">
                        <div class="col-sm-12">
                            <?= $form->field($model, 'from_email')->textInput()->label(Yii::t('podium/view', 'Podium "From" email address')) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <?= $form->field($model, 'from_name')->textInput()->label(Yii::t('podium/view', 'Podium "From" email name')) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <?= $form->field($model, 'max_attempts')->textInput()->label(Yii::t('podium/view', 'Maximum number of email sending attempts before giving up')) ?>
                        </div>
                    </div>
                    <h3 id="tokens"><span class="label label-primary"><?= Yii::t('podium/view', 'Tokens') ?></span></h3>
                    <div class="row">
                        <div class="col-sm-12">
                            <?= $form->field($model, 'password_reset_token_expire')->textInput()->label(Yii::t('podium/view', 'Number of seconds for the Password Reset Token to expire')) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <?= $form->field($model, 'email_token_expire')->textInput()->label(Yii::t('podium/view', 'Number of seconds for the Email Change Token to expire')) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <?= $form->field($model, 'activation_token_expire')->textInput()->label(Yii::t('podium/view', 'Number of seconds for the Account Activation Token to expire')) ?>
                        </div>
                    </div>
                    <h3 id="db"><span class="label label-primary"><?= Yii::t('podium/view', 'Database') ?></span></h3>
                    <div class="row">
                        <div class="col-sm-12">
                            <?= $form->field($model, 'version')->textInput(['readonly' => true])->label(Yii::t('podium/view', 'Database version (read only)')) ?>
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-sm-12">
                            <?= Html::submitButton('<span class="glyphicon glyphicon-ok-sign"></span> ' . Yii::t('podium/view', 'Save Settings'), ['class' => 'btn btn-block btn-primary', 'name' => 'save-button']) ?>
                        </div>
                    </div>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <div class="col-md-3 col-sm-8 col-sm-offset-4 col-md-offset-0">
        <a href="<?= Url::to(['admin/clear']) ?>" class="btn btn-danger btn-block"><span class="glyphicon glyphicon-alert"></span> <?= Yii::t('podium/view', 'Clear all cache'); ?></a>
    </div>
</div><br>
