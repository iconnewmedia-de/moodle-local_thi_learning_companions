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
if ($oldversion < 2022101300) {
    $dbman = $DB->get_manager();

    // CREATE NEW TABLE thi_thi_lc_groups.
    $table = new xmldb_table('thi_thi_lc_groups');
    // Add field id.
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, true, true, null);
    // Add field name.
    $table->add_field('name', XMLDB_TYPE_CHAR, '100', null, true, false, null);
    // Add field description.
    $table->add_field('description', XMLDB_TYPE_TEXT, '4000', null, true, false, null);
    // Add field closedgroup.
    $table->add_field('closedgroup', XMLDB_TYPE_INTEGER, '1', null, true, false, '0');
    // Add field cmid.
    $table->add_field('cmid', XMLDB_TYPE_INTEGER, '10', null, true, false, null);
    // Add field createdby.
    $table->add_field('createdby', XMLDB_TYPE_INTEGER, '10', null, true, false, null);
    // Add field timecreated.
    $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, true, false, null);
    // Add field timemodified.
    $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, true, false, null);
    // Add field courseid.
    $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, true, false, null);
    // Add key primary.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }

    // CREATE NEW TABLE thi_lc_keywords.
    $table = new xmldb_table('thi_lc_keywords');
    // Add field id.
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, true, true, null);
    // Add field keyword.
    $table->add_field('keyword', XMLDB_TYPE_CHAR, '60', null, true, false, null);
    // Add key primary.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }

    // CREATE NEW TABLE thi_thi_lc_groups_keywords.
    $table = new xmldb_table('thi_thi_lc_groups_keywords');
    // Add field id.
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, true, true, null);
    // Add field groupid.
    $table->add_field('groupid', XMLDB_TYPE_INTEGER, '10', null, true, false, null);
    // Add field keywordid.
    $table->add_field('keywordid', XMLDB_TYPE_INTEGER, '10', null, true, false, null);
    // Add key primary.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
    $table->add_index('group_keyword', XMLDB_INDEX_UNIQUE, ['groupid', 'keywordid']);
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }

    // CREATE NEW TABLE thi_lc_group_members.
    $table = new xmldb_table('thi_lc_group_members');
    // Add field id.
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, true, true, null);
    // Add field groupid.
    $table->add_field('groupid', XMLDB_TYPE_INTEGER, '10', null, true, false, null);
    // Add field userid.
    $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, true, false, null);
    // Add field joined.
    $table->add_field('joined', XMLDB_TYPE_INTEGER, '10', null, true, false, null);
    // Add key primary.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
    $table->add_index('groupid', XMLDB_INDEX_NOTUNIQUE, ['groupid']);
    $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, ['userid']);
    $table->add_index('group_user', XMLDB_INDEX_UNIQUE, ['groupid', 'userid']);
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }

    upgrade_plugin_savepoint(true, 2022101300, 'local', 'thi_learning_companions');
}
