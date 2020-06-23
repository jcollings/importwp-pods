<?php

use ImportWP\EventHandler;
use ImportWPAddon\Pods\Importer\Template\Pods;

function iwp_pods_register_events(EventHandler $event_handler)
{
    $pods = new Pods($event_handler);
}

add_action('iwp/register_events', 'iwp_pods_register_events');
