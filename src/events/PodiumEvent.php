<?php

namespace bizley\podium\events;

/**
 * Event names
 *
 * @author David Newcomb <david.newcomb@bigsoft.co.uk>
 * @since 0.8
 */
class PodiumEvent {

	const THREAD_CREATED = 'podium_event_thread_created';
	const THREAD_UPDATED = 'podium_event_thread_updated';
	const THREAD_DELETED = 'podium_event_thread_deleted';
}