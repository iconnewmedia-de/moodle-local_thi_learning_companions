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

/**
 * Das Projekt THISuccessAI (FBM202-EA-1690-07540) wird im Rahmen der Förderlinie „Hochschulen durch Digitalisierung stärken“
 * durch die Stiftung Innovation in der Hochschulehre gefördert.
 *
 * @package     local_thi_learning_companions
 * @copyright   2022 ICON Vernetzte Kommunikation GmbH <info@iconnewmedia.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('AJAX_SCRIPT', true);
require_once(dirname(__DIR__, 3). '/config.php');
require_login();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
$context = context_system::instance();
$PAGE->set_context($context);
$groupid = optional_param('groupId', 0, PARAM_INT);
$lastpostid = required_param('lastPostId', PARAM_INT);
$questionid = optional_param('questionid', 0, PARAM_INT);
if ($questionid > 0) {
    $chat = \local_thi_learning_companions\chat::create_question_chat($questionid);
} else {
    $chat = \local_thi_learning_companions\chat::create_group_chat($groupid);
}
$posts = $chat->get_newest_posts($lastpostid);

echo json_encode(['posts' => array_values($posts)]);
