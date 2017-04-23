<?php

namespace bizley\podium\slugs;

use yii\behaviors\SluggableBehavior;

/**
 * Slug generator behavior
 *
 * To implement your own slug generators create a class that extends
 * PodiumSluggableBehavior and override SluggableBehavior::generateSlug().
 *
 * For example add to configuration:
 *
 * ```
 * 'modules' => [
 *    'podium' => [
 *       'class' => 'bizley\podium\Podium',
 *          'slugGenerator' => MyPodiumSlugGenerator::className(),
 * ```
 *
 * and then create a class:
 *
 * ```
 * class MyPodiumSlugGenerator extends PodiumSluggableBehavior
 * {
 *
 *    protected function generateSlug($slugParts)
 *    {
 *       if ($this->type == self::THREAD) {
 *          $s = substr($slugParts[0], 1);
 *          return str_replace("/", "-", $s);
 *       } else {
 *          return parent::generateSlug ( $slugParts );
 *       }
 *    }
 * }
 * ```
 *
 * @author David Newcomb <david.newcomb@bigsoft.co.uk>
 * @since 0.8
 */

class PodiumSluggableBehavior extends SluggableBehavior
{

    const CATEGORY = "category";
    const FORUM = "forum";
    const THREAD = "thread";
    const USER = "user";

    /**
     * @var string Use PodiumSluggableBehavior::CATEGORY,...
     */
    public $type;

}
