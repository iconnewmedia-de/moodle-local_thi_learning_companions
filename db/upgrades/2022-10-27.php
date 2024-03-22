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
global $DB;

if ($oldversion < 2022102801) {
    $dbman = $DB->get_manager();

    // CREATE NEW TABLE thi_lc_mentor_questions.
    $table = new xmldb_table('thi_lc_mentor_questions');
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, true, true);
    $table->add_field('askedby', XMLDB_TYPE_INTEGER, '10', null, true, false);
    $table->add_field('mentorid', XMLDB_TYPE_INTEGER, '10', null, false, false);
    $table->add_field('question', XMLDB_TYPE_TEXT, '', null, true, false);
    $table->add_field('timeclosed', XMLDB_TYPE_INTEGER, '10', null, false, false);
    $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, true, false);
    $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }

    // CREATE NEW TABLE thi_lc_mentor_answers.
    $table = new xmldb_table('thi_lc_mentor_answers');
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, true, true);
    $table->add_field('questionid', XMLDB_TYPE_INTEGER, '10', null, true, false);
    $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, true, false);
    $table->add_field('answer', XMLDB_TYPE_TEXT, '', null, true, false);
    $table->add_field('issolution', XMLDB_TYPE_INTEGER, '1', null, true, false, 0);
    $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, true, false);
    $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }

    upgrade_plugin_savepoint(true, 2022102801, 'local', 'thi_learning_companions');
}
