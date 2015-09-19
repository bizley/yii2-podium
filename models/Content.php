<?php

namespace bizley\podium\models;

use bizley\podium\components\Helper;
use yii\db\ActiveRecord;
use yii\helpers\HtmlPurifier;

/**
 * Content model
 *
 * @property integer $id
 * @property string $name
 * @property string $topic
 * @property string $content
 */
class Content extends ActiveRecord
{

    const TERMS_TITLE = 'Forum Terms and Conditions';
    const TERMS_BODY  = 'Please remember that we are not responsible for any messages posted. We do not vouch for or warrant the accuracy, completeness or usefulness of any post, and are not responsible for the contents of any post.<br /><br />The posts express the views of the author of the post, not necessarily the views of this forum. Any user who feels that a posted message is objectionable is encouraged to contact us immediately by email. We have the ability to remove objectionable posts and we will make every effort to do so, within a reasonable time frame, if we determine that removal is necessary.<br /><br />You agree, through your use of this service, that you will not use this forum to post any material which is knowingly false and/or defamatory, inaccurate, abusive, vulgar, hateful, harassing, obscene, profane, sexually oriented, threatening, invasive of a person\'s privacy, or otherwise violative of any law.<br /><br />You agree not to post any copyrighted material unless the copyright is owned by you or by this forum.';
    const REG_TITLE   = 'Welcome to {forum}! This is your activation link';
    const REG_BODY    = '<p>Thank you for registering at {forum}!</p><p>To activate you account open the following link in your Internet browser:<br />{link}<br /></p><p>See you soon!<br />{forum}</p>';
    const PASS_TITLE  = '{forum} password reset link';
    const PASS_BODY   = '<p>{forum} Password Reset</p><p>You are receiving this e-mail because someone has started the process of changing the account password at {forum}.<br />If this person is you open the following link in your Internet browser and follow the instructions on screen.</p><p>{link}</p><p>If it was not you just ignore this e-mail.</p><p>Thank you!<br />{forum}</p>';
    const REACT_TITLE = '{forum} account reactivation';
    const REACT_BODY  = '<p>{forum} Account Activation</p><p>You are receiving this e-mail because someone has started the process of activating the account at {forum}.<br />If this person is you open the following link in your Internet browser and follow the instructions on screen.</p><p>{link}</p><p>If it was not you just ignore this e-mail.</p><p>Thank you!<br />{forum}</p>';
    const NEW_TITLE   = 'New e-mail activation link at {forum}';
    const NEW_BODY    = '<p>{forum} New E-mail Address Activation</p><p>To activate your new e-mail address open the following link in your Internet browser and follow the instructions on screen.</p><p>{link}</p><p>Thank you<br />{forum}</p>';
    const SUB_TITLE   = 'New post in subscribed thread at {forum}';
    const SUB_BODY    = '<p>There has been new post added in the thread you are subscribing. Click the following link to read the thread.</p><p>{link}</p><p>See you soon!<br />{forum}</p>';
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%podium_content}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content', 'topic'], 'required'],
            [['content', 'topic'], 'string', 'min' => 1],
            ['topic', 'filter', 'filter' => function($value) { return HtmlPurifier::process($value); }],
            ['content', 'filter', 'filter' => function($value) { return HtmlPurifier::process($value, Helper::podiumPurifierConfig('full')); }],
        ];
    }
}