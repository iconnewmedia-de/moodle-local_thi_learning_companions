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

if ($oldversion < 2023013001) {
    $dbman = $DB->get_manager();

    $table = new xmldb_table('thi_lc_chat');
    $dbman->drop_index($table, new xmldb_index('lcchat_cou_uix', XMLDB_INDEX_NOTUNIQUE, ['course']));

    upgrade_plugin_savepoint(true, 2023013001, 'local', 'thi_learning_companions');
}

if ($oldversion < 2023013002) {
    $dbman = $DB->get_manager();

    $table = new xmldb_table('thi_lc_chat_comment');
    $dbman->drop_index($table, new xmldb_index('lcchatcomm_use_uix', XMLDB_INDEX_NOTUNIQUE, ['userid']));
    $dbman->drop_index($table, new xmldb_index('lcchatcomm_cha_uix', XMLDB_INDEX_NOTUNIQUE, ['chatid']));

    upgrade_plugin_savepoint(true, 2023013002, 'local', 'thi_learning_companions');
}
