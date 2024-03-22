<?php
if ($oldversion < 2022102102) {
    $dbman = $DB->get_manager();

// MODIFY TABLE thi_thi_lc_groups.
    $table = new xmldb_table('thi_thi_lc_groups');
// ------------- modify field cmid — changes made: Default value was changed from  to 0
    $field = new xmldb_field('cmid');
    $field->set_attributes($type=XMLDB_TYPE_INTEGER, $length='10', $unsigned=true, $notnull=true, $sequence=false, $default='0', $previous='closedgroup');
    $dbman->change_field_type($table, $field);

    upgrade_plugin_savepoint(true, 2022102102, 'local', 'thi_learning_companions');
}
if ($oldversion < 2022102103) {

        $dbman = $DB->get_manager();

// MODIFY TABLE thi_lc_group_members.
        $table = new xmldb_table('thi_lc_group_members');
// ------------ add field isadmin
        $field = new xmldb_field('isadmin');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '1', NULL, true, false, '0', NULL);
        $dbman->add_field($table, $field);

        upgrade_plugin_savepoint(true, 2022102103, 'local', 'thi_learning_companions');
    }
