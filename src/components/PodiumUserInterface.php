<?php

namespace bizley\podium\components;

use yii\web\IdentityInterface;

interface PodiumUserInterface extends IdentityInterface
{
    
    public function getPodiumAnonymous();
    
    public function getPodiumCreatedAt();
    
    public function getPodiumEmail();
    
    public function getPodiumId();
    
    public function getPodiumModerators();
    
    public function getPodiumName();
    
    /**
     * @return \yii\db\ActiveQuery with $limit newest User records
     */
    public function getPodiumNewest($limit = 10);
    
    public function getPodiumRole();
    
    public function getPodiumSlug();
    
    public function getPodiumStatus();
    
    public function getPodiumTag($simple = false);
    
    public function getPodiumTimeZone();
    
    /**
     * Declares a `has-many` relation.
     * The declaration is returned in terms of a relational [[ActiveQuery]] instance
     * through which the related record can be queried and retrieved back.
     * @see [[BaseActiveRecord::hasManu()]]
     *
     * @param string $class the class name of the related record
     * @param array $link the primary-foreign key constraint. The keys of the array refer to
     * the attributes of the record associated with the `$class` model, while the values of the
     * array refer to the corresponding attributes in **this** AR class.
     * @return ActiveQueryInterface the relational query object.
     */
    public function hasMany($class, $link);
    
    /**
     * Declares a `has-one` relation.
     * The declaration is returned in terms of a relational [[ActiveQuery]] instance
     * through which the related record can be queried and retrieved back.
     * @see [[BaseActiveRecord::hasOne()]]
     *
     * @param string $class the class name of the related record
     * @param array $link the primary-foreign key constraint. The keys of the array refer to
     * the attributes of the record associated with the `$class` model, while the values of the
     * array refer to the corresponding attributes in **this** AR class.
     * @return ActiveQueryInterface the relational query object.
     */
    public function hasOne($class, $link);
    
    public function podiumBan();
    
    public function podiumDelete();
    
    public function podiumDemoteTo($role);
    
    public function podiumFindModerator($id);
    
    public function podiumFindOne($id);
    
    public function podiumPromoteTo($role);
    
    public function podiumUnban();
    
    public function podiumUserSearch($params, $active = false, $mods = false);
}