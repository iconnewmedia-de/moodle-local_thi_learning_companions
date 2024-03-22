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
if ($oldversion < 2022101900) {
    $dbman = $DB->get_manager();

    // CREATE NEW TABLE thi_lc_mentors.
    $table = new xmldb_table('thi_lc_mentors');
    // Add field id.
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, true, true, null);
    // Add field userid.
    $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, true, false, null);
    // Add field topic.
    $table->add_field('topic', XMLDB_TYPE_CHAR, '255', null, false, false, null);
    // Add key primary.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
    $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, ['userid']);
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }

    // CREATE NEW TABLE thi_lc_users_mentors.
    $table = new xmldb_table('thi_lc_users_mentors');
    // Add field id.
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, true, true, null);
    // Add field userid.
    $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, true, false, null);
    // Add field mentorid.
    $table->add_field('mentorid', XMLDB_TYPE_INTEGER, '10', null, false, false, null);
    // Add key primary.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
    // Add key fk_mentorid.
    $table->add_key('fk_mentorid', XMLDB_KEY_FOREIGN, ['mentorid']);
    $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, ['userid']);
    $table->add_index('useridmentor', XMLDB_INDEX_NOTUNIQUE, ['userid']);
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }

    // CREATE NEW TABLE thi_lc_chat.
    $table = new xmldb_table('thi_lc_chat');
    // Add field id.
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, true, true, null);
    // Add field course.
    $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, true, false, null);
    // Add field chattype.
    $table->add_field('chattype', XMLDB_TYPE_INTEGER, '2', null, true, false, '0');
    // Add field relatedid.
    $table->add_field('relatedid', XMLDB_TYPE_INTEGER, '10', null, true, false, null);
    // Add field timecreated.
    $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, false, false, null);
    // Add key primary.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
    $table->add_index('chattype_relatedid', XMLDB_INDEX_UNIQUE, ['chattype,relatedid']);
    $table->add_index('course', XMLDB_INDEX_NOTUNIQUE, ['course']);
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }

    // CREATE NEW TABLE thi_lc_chat_comment.
    $table = new xmldb_table('thi_lc_chat_comment');
    // Add field id.
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, true, true, null);
    // Add field chatid.
    $table->add_field('chatid', XMLDB_TYPE_INTEGER, '10', null, true, false, null);
    // Add field userid.
    $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, true, false, null);
    // Add field flagged.
    $table->add_field('flagged', XMLDB_TYPE_INTEGER, '1', null, true, false, '0');
    // Add field totalscore.
    $table->add_field('totalscore', XMLDB_TYPE_INTEGER, '5', null, true, false, '0');
    // Add field timecreated.
    $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, true, false, null);
    // Add field timemodified.
    $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, false, false, null);
    // Add key primary.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
    $table->add_index('chatid', XMLDB_INDEX_NOTUNIQUE, ['chatid']);
    $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, ['userid']);
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }
    upgrade_plugin_savepoint(true, 2022101900, 'local', 'thi_learning_companions');
}
