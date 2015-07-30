<?php

namespace bizley\podium\components;

interface PodiumUserInterface
{
    /**
     * @return \yii\db\ActiveQuery with $limit newest User records
     */
    public function getNewest($limit = 10);
}