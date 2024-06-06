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

global $USER, $PAGE;

$PAGE->set_context(context_system::instance());

$previewgroup = optional_param('shouldIncludeId', null, PARAM_INT);

$groups = local_thi_learning_companions\groups::get_groups_of_user($USER->id, $previewgroup);
foreach ($groups as $group) {
    $group->comments_since_last_visit = \local_thi_learning_companions\groups::count_comments_since_last_visit($group->id);
    $group->has_new_comments = $group->comments_since_last_visit > 0;
    $lastcomment = strip_tags($group->get_last_comment());
    $group->lastcomment = $lastcomment;
    if (strlen($lastcomment) > 100) {
        $group->lastcomment = substr($lastcomment, 0, 97).'...';
    }
}
header('Content-Type: application/json');
$response = json_encode(["groups" => $groups]);
echo $response;
