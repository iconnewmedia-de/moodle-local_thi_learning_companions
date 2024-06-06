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

require_once(dirname(__DIR__, 2) . '/config.php');
require_once(__DIR__ . "/locallib.php");

require_login();
global $PAGE, $CFG, $OUTPUT, $USER;
$PAGE->set_context(\context_system::instance());
$PAGE->set_url($CFG->wwwroot . '/local/thi_learning_companions/chat.php');
$defaultlayout = 'base';
$layout = optional_param('layout', $defaultlayout, PARAM_TEXT);
$layoutwhitelist = ['base', 'standard', 'course', 'incourse', 'popup', 'embedded'];
if (!in_array($layout, $layoutwhitelist)) {
    $layout = $defaultlayout;
}

$groupid = optional_param('groupid', null, PARAM_INT);
$group = new \local_thi_learning_companions\group($groupid);
$action = optional_param('action', null, PARAM_TEXT);
$mayviewgroup = \local_thi_learning_companions\groups::may_view_group($groupid);
if ($group->closedgroup && !$mayviewgroup) {
    \local_thi_learning_companions\chat::redirect_to_other_group_chat();
}

$PAGE->set_pagelayout($layout);
$PAGE->set_title(get_string('learninggroups', 'local_thi_learning_companions'));
$groupid = optional_param('groupid', null, PARAM_INT);
$PAGE->requires->js_call_amd('local_thi_learning_companions/thi_learning_companions_chat', 'init');

$PAGE->requires->js(new moodle_url('https://unpkg.com/react@18.2.0/umd/react.production.min.js'), true);
$PAGE->requires->js(new moodle_url('https://unpkg.com/react-dom@18.2.0/umd/react-dom.production.min.js'), true);
$PAGE->requires->js(new moodle_url('/local/thi_learning_companions/js/react/build/thi_learning_companions-chat.min.js'));

$chat = \local_thi_learning_companions\chat::create_group_chat($groupid);

echo $OUTPUT->header();
if (!empty($action)) {
    // Using switch/case just in case we might add further actions later.
    switch ($action) {
        case "invite":
            \local_thi_learning_companions\invite_users();
            break;
        default:
            // Nothing to do. Only using switch/case in case we'll have more actions in the future.
    }
}
echo $chat->get_chat_module();
echo $OUTPUT->footer();
