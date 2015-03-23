<p><strong><?= Yii::t('podium/mail', '{NAME} Password Reset', ['NAME' => $forum]) ?></strong></p>
<p><?= Yii::t('podium/mail', 'You are receiving this e-mail because someone has started the process of changing the account password at {NAME}', ['NAME' => $forum]) ?></p>
<p><?= Yii::t('podium/mail', 'If this person was you open the following link in your Internet browser and follow the instructions on screen.') ?></p>
<p><a href="<?= $link ?>"><?= $link ?></a></p>
<p><?= Yii::t('podium/mail', 'If it was not you just ignore this e-mail.') ?></p>
<p><?= Yii::t('podium/mail', 'Thank you!') ?></p>
<p><em><?= $forum ?></em></p>