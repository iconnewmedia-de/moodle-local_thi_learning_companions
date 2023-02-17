<?php
defined('MOODLE_INTERNAL') || die();

global $CFG, $DB;

if ($oldversion < 2023021700) {
    $dbman = $DB->get_manager();
    $table = new xmldb_table('lc_mentor_questions');
    $field = new xmldb_field('topic', XMLDB_TYPE_CHAR, 255);
    $dbman->change_field_type($table, $field);
    $dbman->change_field_precision($table, $field);
    upgrade_plugin_savepoint(true, 2023021700, 'local', 'learningcompanions');
}