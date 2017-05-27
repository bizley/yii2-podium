<?php

namespace bizley\podium\events;

use bizley\podium\Podium;

/**
 * Creates and sends events
 *
 * @author David Newcomb <david.newcomb@bigsoft.co.uk>
 * @since 0.8
 */
class EventSender {

	public static function threadCreated($thread) {
		self::threadEvent(PodiumEvent::THREAD_CREATED, $thread);
	}

	public static function threadUpdated($thread) {
		self::threadEvent(PodiumEvent::THREAD_UPDATED, $thread);
	}

	public static function threadDeleted($thread) {
		self::threadEvent(PodiumEvent::THREAD_DELETED, $thread);
	}

	private static function threadEvent($eventType, $thread) {
		$event = new PodiumThreadEvent();
		$event->thread = $thread;
		Podium::getInstance()->trigger($eventType, $event);
	}
}
