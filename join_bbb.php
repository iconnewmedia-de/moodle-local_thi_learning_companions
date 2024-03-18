<?php
require_once __DIR__ . '/../../config.php';

require_once $CFG->dirroot . "/local/thi_learning_companions/classes/meeting.php";
require_once $CFG->dirroot . "/local/thi_learning_companions/classes/instance.php";
//use mod_bigbluebuttonbn\instance;
use local_thi_learning_companions\instance;
use mod_bigbluebuttonbn\logger;
use local_thi_learning_companions\meeting;
$meetingid = required_param('id', PARAM_INT);
$meetingobj = $DB->get_record('lc_bbb', array('id' => $meetingid));
$groupid = $meetingobj->groupid;

$bbbInstance = new instance($groupid, $meetingobj);
//$meetingObj = new meeting($bbbInstance);
//$meeting = $meetingObj->create_meeting();

$origin = logger::ORIGIN_BASE;
$url = meeting::join_meeting($bbbInstance, $origin);
$debug = 'me';