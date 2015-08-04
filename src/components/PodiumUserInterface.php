<?php

namespace bizley\podium\components;

interface PodiumUserInterface
{
    
    public function getPodiumAnonymous();
    
    public function getPodiumEmail();
    
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
    
    public function podiumBan();
    
    public function podiumDelete();
    
    public function podiumDemoteTo($role);
    
    public function podiumFindModerator($id);
    
    public function podiumFindOne($id);
    
    public function podiumPromoteTo($role);
    
    public function podiumUnban();
    
    public function podiumUserSearch($params, $active = false, $mods = false);
}