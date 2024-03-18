<?php
define('AJAX_SCRIPT', true);
require_once __DIR__ . "/../../../config.php";
require_once $CFG->dirroot . "/local/thi_learning_companions/classes/meeting.php";
require_once $CFG->dirroot . "/local/thi_learning_companions/classes/instance.php";
//use mod_bigbluebuttonbn\instance;
use local_thi_learning_companions\instance;
use mod_bigbluebuttonbn\logger;
use local_thi_learning_companions\meeting;
$chatid = required_param('chatid', PARAM_INT);
$groupid = \local_thi_learning_companions\groups::get_groupid_of_chatid($chatid);
$meeting = instance::create_meeting($groupid);

$bbbInstance = new instance($groupid, $meeting);
//$meetingObj = new meeting($bbbInstance);
//$meeting = $meetingObj->create_meeting();

$origin = logger::ORIGIN_BASE;
$url = meeting::join_meeting($bbbInstance, $origin);

echo json_encode(['moderator' => $url, 'participants' => $CFG->wwwroot . '/local/thi_learning_companions/join_bbb.php?id=' . $meeting->id]);