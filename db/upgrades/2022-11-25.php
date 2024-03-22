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
defined('MOODLE_INTERNAL') || die();

global $CFG, $DB;

if ($oldversion < 2022112506) {
    require_once(__DIR__ . '/../lib.php');;
    local_thi_learning_companions\db\create_status_profile_field();

    upgrade_plugin_savepoint(true, 2022112506, 'local', 'thi_learning_companions');
}

if ($oldversion < 2022112507) {
    $dbman = $DB->get_manager();

    // MODIFY TABLE thi_lc_mentor_questions.
    $table = new xmldb_table('thi_lc_mentor_questions');
    // Add field 'topic'.
    $field = new xmldb_field('topic');
    $field->set_attributes(XMLDB_TYPE_INTEGER, 10, null, true, false, 0);
    $dbman->add_field($table, $field);

    upgrade_plugin_savepoint(true, 2022112507, 'local', 'thi_learning_companions');
}
