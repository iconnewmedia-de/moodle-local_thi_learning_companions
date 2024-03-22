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
require_once('../../../config.php');
require_once('../lib.php');
require_login();
$context = context_system::instance();
require_capability( 'local/thi_learning_companions:group_view', $context);

$PAGE->set_context($context);
$PAGE->set_url($CFG->wwwroot.'/local/thi_learning_companions/group/index.php');
$PAGE->set_pagelayout('standard');
$PAGE->requires->js_call_amd('local_thi_learning_companions/group', 'init');
$PAGE->requires->css('/local/thi_learning_companions/js_lib/DataTables/datatables.min.css');
$PAGE->requires->css('/local/thi_learning_companions/js_lib/balloon.css');

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_thi_learning_companions/group/group_index', [
    'cfg' => $CFG,
]);
echo $OUTPUT->footer();
