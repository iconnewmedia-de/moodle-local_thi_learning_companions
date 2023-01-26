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
// $url will be like:
// https://test-moodle.blindsidenetworks.com/bigbluebutton/api/join?meetingID=5ec9a85ada0ea221f74c57e7a657b8bfa7b91d2c-4-1%5B0%5D&fullName=Admin+User&password=yxrb7dZ6nNXj&logoutURL=http%3A%2F%2Fthi406.local%2Fmod%2Fbigbluebuttonbn%2Fbbb_view.php%3Faction%3Dlogout%26id%3D11&role=MODERATOR&userID=2&createTime=1674649265119&checksum=ebc9b58bcd4e984dc6efbee1483ba7304cf26fb1
//
// broken down into its parameters that's:
https://test-moodle.blindsidenetworks.com/bigbluebutton/api/join?meetingID=5ec9a85ada0ea221f74c57e7a657b8bfa7b91d2c-4-1%5B0%5D&
//fullName=Admin+User&
//password=yxrb7dZ6nNXj&
//logoutURL=http%3A%2F%2Fthi406.local%2Fmod%2Fbigbluebuttonbn%2Fbbb_view.php%3Faction%3Dlogout%26id%3D11&
//role=MODERATOR&
//userID=2&
//checksum=3a07b1d5c5d674746a0ca286b4c6819dd77bdc5d
//
// ICTODO: find out what the url for the participant would be.
// It seems to be like http://thi406.local/mod/bigbluebuttonbn/bbb_view.php?action=join&amp;id=11&amp;bn=1
// BUT: Can we have seperate rooms within one instance of mod_bigbluebuttonbn? Because otherwise we would need to dynamically create a new one every time
// what does the bn=1 mean? Using bn=2 works indeed and gives the user a blank meeeting room. But how can we give the same one to the moderator?
echo json_encode(['moderator' => $url, 'participants' => $url . "&role=participant"]);