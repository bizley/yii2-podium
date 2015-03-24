<p><strong><?= Yii::t('podium/mail', '{NAME} New E-mail Address Activation', ['NAME' => $forum]) ?></strong></p>
<p><?= Yii::t('podium/mail', 'To activate your new e-mail address open the following link in your Internet browser and follow the instructions on screen.') ?></p>
<p><a href="<?= $link ?>"><?= $link ?></a></p>
<p><em><?= $forum ?></em></p>