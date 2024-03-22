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

if ($oldversion < 2023021700) {
    $dbman = $DB->get_manager();
    $table = new xmldb_table('thi_lc_mentor_questions');
    $field = new xmldb_field('topic', XMLDB_TYPE_CHAR, 255);
    $dbman->change_field_type($table, $field);
    $dbman->change_field_precision($table, $field);
    upgrade_plugin_savepoint(true, 2023021700, 'local', 'thi_learning_companions');
}
