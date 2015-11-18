<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\components;

use yii\web\IdentityInterface;

/**
 * PodiumUser interface
 * 
 * This interface must be implemented by class providing User Identity for 
 * Podium.
 * By default it is implemented by [[\bizley\podium\models\User]] but by setting 
 * [[\bizley\podium\Module::$userComponent]] to 'inherit' forum can take app's 
 * User Identity class as own as long as it implements this interface.
 */
interface PodiumUserInterface extends IdentityInterface
{
    
    /**
     * @return integer Whether user should be hidden on forum.
     */
    public function getPodiumAnonymous();
    
    /**
     * @return integer Account create date.
     */
    public function getPodiumCreatedAt();
    
    /**
     * @return string User's email address.
     */
    public function getPodiumEmail();
    
    /**
     * @return integer User's ID.
     */
    public function getPodiumId();
    
    /**
     * @return string User's ID attribute.
     */
    public function getPodiumIdAttribute();
    
    /**
     * @return \yii\db\ActiveQuery Podium's moderators.
     */
    public function getPodiumModerators();
    
    /**
     * @return string User's username.
     */
    public function getPodiumName();
    
    /**
     * @return \yii\db\ActiveQuery with $limit newest User records
     */
    public function getPodiumNewest($limit = 10);
    
    /**
     * @return integer User's role.
     */
    public function getPodiumRole();
    
    /**
     * @return string User's slug.
     */
    public function getPodiumSlug();
    
    /**
     * @return integer User's status.
     */
    public function getPodiumStatus();
    
    /**
     * @param boolean $simple Whether tag should be simplified.
     * @return string User's Podium name tag.
     */
    public function getPodiumTag($simple = false);
    
    /**
     * @return string User's timezone.
     */
    public function getPodiumTimeZone();
    
    /**
     * Declares a `has-many` relation.
     * @see [[BaseActiveRecord::hasMany()]]
     * @param string $class the class name of the related record
     * @param array $link the primary-foreign key constraint. The keys of the array refer to
     * the attributes of the record associated with the `$class` model, while the values of the
     * array refer to the corresponding attributes in **this** AR class.
     * @return ActiveQueryInterface the relational query object.
     */
    public function hasMany($class, $link);
    
    /**
     * Declares a `has-one` relation.
     * @see [[BaseActiveRecord::hasOne()]]
     * @param string $class the class name of the related record
     * @param array $link the primary-foreign key constraint. The keys of the array refer to
     * the attributes of the record associated with the `$class` model, while the values of the
     * array refer to the corresponding attributes in **this** AR class.
     * @return ActiveQueryInterface the relational query object.
     */
    public function hasOne($class, $link);
    
    /**
     * Bans Podium account.
     * @return boolean
     */
    public function podiumBan();
    
    /**
     * Deletes Podium account.
     * @return boolean
     */
    public function podiumDelete();
    
    /**
     * Demotes Podium account to given role.
     * @param integer $role
     * @return boolean
     */
    public function podiumDemoteTo($role);
    
    /**
     * Finds Podium moderator of given ID.
     * @param integer ID.
     * @return ActiveRecord
     */
    public function podiumFindModerator($id);
    
    /**
     * Finds Podium User of given ID.
     * @param integer ID.
     * @return PodiumUserInterface
     */
    public function podiumFindOne($id);
    
    /**
     * Promotes Podium account to given role.
     * @param integer $role
     * @return boolean
     */
    public function podiumPromoteTo($role);
    
    /**
     * Unbans Podium account.
     * @return boolean
     */
    public function podiumUnban();
    
    /**
     * Returns search array for Podium Users with $searchModel and $dataProvider
     * @param array $params User attributes to look for.
     * @param boolean $active Whether to look for Users with status different than \bizley\podium\models\User::STATUS_REGISTERED
     * @param boolean $mods Whether to look for Users with roles \bizley\podium\models\User::ROLE_ADMIN or \bizley\podium\models\User::ROLE_MODERATOR
     * @return [$searchModel, $dataProvider]
     */
    public function podiumUserSearch($params, $active = false, $mods = false);
}