<?php

namespace bizley\podium\slugs;

use yii\behaviors\SluggableBehavior;

/**
 * Slug generator model
 *
 * To implement your own slug generators create a class that extends
 * PodiumSluggableBehavior and override SluggableBehavior::generateSlug().
 *
 * @author David Newcomb <david.newcomb@bigsoft.co.uk>
 * @since x.x
 *
 */

class PodiumSluggableBehavior extends SluggableBehavior {

    const CATEGORY = "category";
    const FORUM = "forum";
    const THREAD = "thread";
    const USER = "user";

    /**
     * @var string Use PodiumSluggableBehavior::CATEGORY,...
     */
    public $type;

}
