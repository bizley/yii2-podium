<?php

namespace bizley\podium\events;

use yii\base\Event;

/**
 * Thread event
 *
 * @author David Newcomb <david.newcomb@bigsoft.co.uk>
 * @since 0.8
 */
class PodiumThreadEvent extends Event {

	public $thread;

}