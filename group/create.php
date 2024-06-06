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

require_once(dirname(__DIR__, 3).'/config.php');

require_login();
global $PAGE, $CFG, $OUTPUT;
$PAGE->set_context(\context_system::instance());
$PAGE->set_url($CFG->wwwroot . '/local/thi_learning_companions/creategroup.php');
$defaultlayout = 'base';
$layout = optional_param('layout', $defaultlayout, PARAM_TEXT);
$layoutwhitelist = ['base', 'standard', 'course', 'incourse', 'popup', 'embedded'];
if (!in_array($layout, $layoutwhitelist)) {
    $layout = $defaultlayout;
}
$PAGE->set_pagelayout($layout);
$PAGE->set_title(get_string('creategroup', 'local_thi_learning_companions'));
$PAGE->navbar->add(
    get_string('navbar_groups', 'local_thi_learning_companions'),
    new moodle_url('/local/thi_learning_companions/group/index.php')
);
$PAGE->navbar->add(
    get_string('navbar_create_group', 'local_thi_learning_companions'),
    new moodle_url('/local/thi_learning_companions/group/create.php')
);

$cmid = optional_param('cmid', 0, PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);
// ICTODO: check that the user has the permission to create a group for that course.

$form = new \local_thi_learning_companions\forms\create_edit_group_form(
    null,
    [
        'cmid' => $cmid,
        'courseid' => $courseid,
    ]
);
if ($data = $form->get_data()) {
    try {
        $groupid = \local_thi_learning_companions\groups::group_create($data);
        if ($layout === 'popup' || $layout === 'embedded' ) {
            echo "<script>document.querySelector('.modal').dispatchEvent((new Event('modal:hidden')))</script>";
        } else {
            redirect(
                new moodle_url('/local/thi_learning_companions/chat.php?groupid=' . $groupid),
                get_string('group_created', 'local_thi_learning_companions')
            );
        }
    } catch (Exception $e) {
        $warning = new \core\output\notification(
            get_string('error_group_creation_failed', 'local_thi_learning_companions', $e->getMessage()),
            \core\output\notification::NOTIFY_ERROR
        );
    }
    // ICTODO: handle exceptions and output a warning if exception thrown or groupid === false.
    // ICTODO: redirect user if everything went well and output a success message.
}
echo $OUTPUT->header();
if (isset($warning)) {
    echo $OUTPUT->render($warning);
}
echo $form->render();
echo $OUTPUT->footer();
