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
if ($oldversion < 2022102102) {
    $dbman = $DB->get_manager();

    // MODIFY TABLE thi_thi_lc_groups.
    $table = new xmldb_table('thi_thi_lc_groups');
    // Modify field cmid — changes made: Default value was changed from  to 0.
    $field = new xmldb_field('cmid');
    $field->set_attributes(
        $type = XMLDB_TYPE_INTEGER,
        $length = '10',
        $unsigned = true,
        $notnull = true,
        $sequence = false,
        $default = '0',
        $previous = 'closedgroup'
    );
    $dbman->change_field_type($table, $field);

    upgrade_plugin_savepoint(true, 2022102102, 'local', 'thi_learning_companions');
}
if ($oldversion < 2022102103) {

        $dbman = $DB->get_manager();

    // MODIFY TABLE thi_lc_group_members.
        $table = new xmldb_table('thi_lc_group_members');
    // Add field isadmin.
        $field = new xmldb_field('isadmin');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '1', null, true, false, '0', null);
        $dbman->add_field($table, $field);

        upgrade_plugin_savepoint(true, 2022102103, 'local', 'thi_learning_companions');
}
