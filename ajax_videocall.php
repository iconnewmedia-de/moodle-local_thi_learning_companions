<?php
define('AJAX_SCRIPT', true);
require_once __DIR__ . "/../../config.php";
require_once $CFG->dirroot . "/mod/bigbluebuttonbn/classes/meeting.php";
use mod_bigbluebuttonbn\instance;
use mod_bigbluebuttonbn\logger;
use mod_bigbluebuttonbn\meeting;

$bbbInstance = $instance = instance::get_from_cmid(11);
$meetingObj = new \mod_bigbluebuttonbn\meeting($bbbInstance);
$meeting = $meetingObj->create_meeting();

$origin = logger::ORIGIN_BASE;
$url = meeting::join_meeting($instance, $origin);

echo json_encode(['moderator' => $url, 'participants' => $url . "&role=participant"]);