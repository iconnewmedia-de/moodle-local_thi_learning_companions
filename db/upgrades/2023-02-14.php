<?php

defined('MOODLE_INTERNAL') || die();

global $CFG, $DB;

if ($oldversion < 2023021400) {
    $dbman = $DB->get_manager();
    // Define table lc_bbb to be created.
    $table = new xmldb_table('lc_mentors');
    $field = new xmldb_field('topic');
    if ($dbman->field_exists($table, $field)) {
        $dbman->drop_field($table, $field);
    }
    $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, false);
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }
    upgrade_plugin_savepoint(true, 2023021400, 'local', 'learningcompanions');
}