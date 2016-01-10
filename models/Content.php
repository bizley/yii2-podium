<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\models;

use bizley\podium\components\Helper;
use yii\db\ActiveRecord;
use yii\helpers\HtmlPurifier;

/**
 * Content model
 *
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 * @property integer $id
 * @property string $name
 * @property string $topic
 * @property string $content
 */
class Content extends ActiveRecord
{

    const EMAIL_REACTIVATION = 'email-react';
    const EMAIL_REGISTRATION = 'email-reg';
    const EMAIL_PASSWORD     = 'email-pass';
    const EMAIL_NEW          = 'email-new';
    const EMAIL_SUBSCRIPTION = 'email-sub';
    const TERMS_AND_CONDS    = 'term';
    
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
                'topic'   => Yii::t('podium/view', '{forum} account reactivation'),
                'content' => Yii::t('podium/view', '<p>{forum} Account Activation</p><p>You are receiving this e-mail because someone has started the process of activating the account at {forum}.<br>If this person is you open the following link in your Internet browser and follow the instructions on screen.</p><p>{link}</p><p>If it was not you just ignore this e-mail.</p><p>Thank you!<br>{forum}</p>'),
            ],
            self::EMAIL_REGISTRATION => [
                'topic'   => Yii::t('podium/view', 'Welcome to {forum}! This is your activation link'),
                'content' => Yii::t('podium/view', '<p>Thank you for registering at {forum}!</p><p>To activate you account open the following link in your Internet browser:<br>{link}<br></p><p>See you soon!<br>{forum}</p>'),
            ],
            self::EMAIL_PASSWORD => [
                'topic'   => Yii::t('podium/view', '{forum} password reset link'),
                'content' => Yii::t('podium/view', '<p>{forum} Password Reset</p><p>You are receiving this e-mail because someone has started the process of changing the account password at {forum}.<br>If this person is you open the following link in your Internet browser and follow the instructions on screen.</p><p>{link}</p><p>If it was not you just ignore this e-mail.</p><p>Thank you!<br>{forum}</p>'),
            ],
        ];
        
        if (empty($name)) {
            return $defaults;
        }
        elseif (!empty($defaults[$name])) {
            return $defaults[$name];
        }        
        
        return null;
    }

    /**
     * Returns default content object.
     * @param string $name content's name
     * @return boolean|\bizley\podium\models\Content
     * @since 0.2
     */
    public static function prepareDefault($name)
    {
        $default = static::defaultContent($name);
        
        if (empty($default)) {
            return false;
        }
        
        $content = new Content;
        $content->topic = $default['topic'];
        $content->topic = $default['content'];
        return $content;
    }

    /**
     * Returns email content.
     * @param string $name content's name
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
