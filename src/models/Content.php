<?php

namespace bizley\podium\models;

use bizley\podium\models\db\ContentActiveRecord;
use Yii;

/**
 * Content model
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */
class Content extends ContentActiveRecord
{
    /**
     * Content names.
     */
    const EMAIL_REACTIVATION = 'email-react';
    const EMAIL_REGISTRATION = 'email-reg';
    const EMAIL_PASSWORD     = 'email-pass';
    const EMAIL_NEW          = 'email-new';
    const EMAIL_SUBSCRIPTION = 'email-sub';
    const TERMS_AND_CONDS    = 'terms';

    /**
     * Returns default content array or its element.
     * @param string $name content's name
     * @return array
     * @since 0.2
     */
    public static function defaultContent($name = null)
    {
        $defaults = [
            self::EMAIL_REACTIVATION => [
                'topic' => Yii::t('podium/view', '{forum} account reactivation'),
                'content' => Yii::t('podium/view', '<p>{forum} Account Activation</p><p>You are receiving this e-mail because someone has started the process of activating the account at {forum}.<br>If this person is you open the following link in your Internet browser and follow the instructions on screen.</p><p>{link}</p><p>If it was not you just ignore this e-mail.</p><p>Thank you!<br>{forum}</p>'),
            ],
            self::EMAIL_REGISTRATION => [
                'topic' => Yii::t('podium/view', 'Welcome to {forum}! This is your activation link'),
                'content' => Yii::t('podium/view', '<p>Thank you for registering at {forum}!</p><p>To activate your account open the following link in your Internet browser:<br>{link}<br></p><p>See you soon!<br>{forum}</p>'),
            ],
            self::EMAIL_PASSWORD => [
                'topic' => Yii::t('podium/view', '{forum} password reset link'),
                'content' => Yii::t('podium/view', '<p>{forum} Password Reset</p><p>You are receiving this e-mail because someone has started the process of changing the account password at {forum}.<br>If this person is you open the following link in your Internet browser and follow the instructions on screen.</p><p>{link}</p><p>If it was not you just ignore this e-mail.</p><p>Thank you!<br>{forum}</p>'),
            ],
            self::EMAIL_NEW => [
                'topic' => Yii::t('podium/view', 'New e-mail activation link at {forum}'),
                'content' => Yii::t('podium/view', '<p>{forum} New E-mail Address Activation</p><p>To activate your new e-mail address open the following link in your Internet browser and follow the instructions on screen.</p><p>{link}</p><p>Thank you<br>{forum}</p>'),
            ],
            self::EMAIL_SUBSCRIPTION => [
                'topic' => Yii::t('podium/view', 'New post in subscribed thread at {forum}'),
                'content' => Yii::t('podium/view', '<p>There has been new post added in the thread you are subscribing. Click the following link to read the thread.</p><p>{link}</p><p>See you soon!<br>{forum}</p>'),
            ],
            self::TERMS_AND_CONDS => [
                'topic' => Yii::t('podium/view', 'Forum Terms and Conditions'),
                'content' => Yii::t('podium/view', "Please remember that we are not responsible for any messages posted. We do not vouch for or warrant the accuracy, completeness or usefulness of any post, and are not responsible for the contents of any post.<br><br>The posts express the views of the author of the post, not necessarily the views of this forum. Any user who feels that a posted message is objectionable is encouraged to contact us immediately by email. We have the ability to remove objectionable posts and we will make every effort to do so, within a reasonable time frame, if we determine that removal is necessary.<br><br>You agree, through your use of this service, that you will not use this forum to post any material which is knowingly false and/or defamatory, inaccurate, abusive, vulgar, hateful, harassing, obscene, profane, sexually oriented, threatening, invasive of a person's privacy, or otherwise violative of any law.<br><br>You agree not to post any copyrighted material unless the copyright is owned by you or by this forum."),
            ],
        ];

        if (empty($name)) {
            return $defaults;
        }
        if (!empty($defaults[$name])) {
            return $defaults[$name];
        }
        return null;
    }

    /**
     * Returns default content object.
     * @param string $name content name
     * @return bool|Content
     * @since 0.2
     */
    public static function prepareDefault($name)
    {
        $default = static::defaultContent($name);
        if (empty($default)) {
            return false;
        }

        $content = new static;
        $content->topic = $default['topic'];
        $content->content = $default['content'];
        return $content;
    }

    /**
     * Returns email content.
     * @param string $name content name
     * @return Content
     * @since 0.2
     */
    public static function fill($name)
    {
        $email = Content::find()->where(['name' => $name])->limit(1)->one();
        if (empty($email)) {
            $email = Content::prepareDefault($name);
        }
        return $email;
    }
}
