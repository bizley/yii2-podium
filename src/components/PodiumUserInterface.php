<?php

namespace bizley\podium\components;

interface PodiumUserInterface
{
    
    public function findPodiumOne($id);
    
    public function getPodiumAnonymous();
    
    public function getPodiumEmail();
    
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
    
    public function podiumPromoteTo($role);
    
    public function podiumUnban();
}