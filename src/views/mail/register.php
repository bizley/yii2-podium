<p><strong><?= Yii::t('podium/mail', 'Thank you for registering at {NAME}!', ['NAME' => $forum]) ?></strong></p>
<p><?= Yii::t('podium/mail', 'To activate you account open the following link in your Internet browser:') ?></p>
<p><a href="<?= $link ?>"><?= $link ?></a></p>
<p><?= Yii::t('podium/mail', 'See you soon!') ?></p>
<p><em><?= $forum ?></em></p>