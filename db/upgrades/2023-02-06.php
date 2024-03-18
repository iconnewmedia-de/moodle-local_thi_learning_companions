<?php
defined('MOODLE_INTERNAL') || die();

global $CFG, $DB;

if ($oldversion < 2023020602) {
    $dbman = $DB->get_manager();
    // Define table thi_lc_bbb to be created.
    $table = new xmldb_table('thi_lc_bbb');

    $field = new xmldb_field('moderatorid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

    // Conditionally launch create table for thi_lc_bbb.
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }

    $field = new xmldb_field('groupid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

    // Conditionally launch create table for thi_lc_bbb.
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }

    upgrade_plugin_savepoint(true, 2023020602, 'local', 'thi_learning_companions');
}
