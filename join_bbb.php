<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
require_once(__DIR__ . '/../../config.php');
require_login();

require_once($CFG->dirroot . "/local/thi_learning_companions/classes/meeting.php");
require_once($CFG->dirroot . "/local/thi_learning_companions/classes/instance.php");

use local_thi_learning_companions\instance;
use mod_bigbluebuttonbn\logger;
use local_thi_learning_companions\meeting;
$meetingid = required_param('id', PARAM_INT);
$meetingobj = $DB->get_record('thi_lc_bbb', ['id' => $meetingid]);
$groupid = $meetingobj->groupid;

$bbbinstance = new instance($groupid, $meetingobj);

$origin = logger::ORIGIN_BASE;
$url = meeting::join_meeting($bbbinstance, $origin);
