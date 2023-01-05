<?php

defined('MOODLE_INTERNAL') || die();

global $CFG, $DB;

if ($oldversion < 2023010301) {
    $dbman = $DB->get_manager();

    $table = new xmldb_table('lc_chat_comment');
    $field = new xmldb_field('timedeleted', XMLDB_TYPE_INTEGER, '10', null, false, false, null, 'timecreated');
    $dbman->add_field($table, $field);

    upgrade_plugin_savepoint(true, 2023010301, 'local', 'learningcompanions');
}
