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
if ($oldversion < 2022102000) {
    $dbman = $DB->get_manager();

    // MODIFY TABLE thi_lc_chat_comment.
    $table = new xmldb_table('thi_lc_chat_comment');
    // Add field comment.
    $field = new xmldb_field('comment');
    $field->set_attributes(XMLDB_TYPE_TEXT, null, null, true, false, null, null);
    $dbman->add_field($table, $field);

    upgrade_plugin_savepoint(true, 2022102000, 'local', 'thi_learning_companions');
}
