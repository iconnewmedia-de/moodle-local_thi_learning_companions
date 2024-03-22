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
if ($oldversion < 2022112900) {
    $dbman = $DB->get_manager();
    // CREATE NEW TABLE thi_lc_chat_lastvisited.
    $table = new xmldb_table('thi_lc_chat_lastvisited');
    // Add field id.
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, true, true, null);
    // Add field userid.
    $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, true, false, null);
    // Add field chatid.
    $table->add_field('chatid', XMLDB_TYPE_INTEGER, '10', null, true, false, null);
    // Add field timevisited.
    $table->add_field('timevisited', XMLDB_TYPE_INTEGER, '10', null, true, false, null);
    // Add key primary.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
    $table->add_index('chatid', XMLDB_INDEX_NOTUNIQUE, ['chatid']);
    $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, ['userid']);
    $table->add_index('chatid_userid', XMLDB_INDEX_UNIQUE, ['userid,chatid']);
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }
    upgrade_plugin_savepoint(true, 2022112900, 'local', 'thi_learning_companions');

}

if ($oldversion < 2022112902) {
    // MODIFY TABLE thi_thi_lc_groups.
    $table = new xmldb_table('thi_thi_lc_groups');
    // Add field latestcomment.
    $field = new xmldb_field('latestcomment');
    $field->set_attributes(XMLDB_TYPE_INTEGER, '10', null, false, false, '0', null);
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }
    upgrade_plugin_savepoint(true, 2022112902, 'local', 'thi_learning_companions');

}
