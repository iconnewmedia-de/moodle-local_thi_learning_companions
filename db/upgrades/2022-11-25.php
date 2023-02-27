<?php

defined('MOODLE_INTERNAL') || die();

global $CFG, $DB;

if ($oldversion < 2022112506) {
    require_once __DIR__ . '/../lib.php';
    local_learningcompanions\db\create_status_profile_field();

    upgrade_plugin_savepoint(true, 2022112506, 'local', 'learningcompanions');
}

if ($oldversion < 2022112507) {
    $dbman = $DB->get_manager();

    // ##################### MODIFY TABLE lc_mentor_questions
    $table = new xmldb_table('lc_mentor_questions');
    // ------------ add field 'topic'
    $field = new xmldb_field('topic');
    $field->set_attributes(XMLDB_TYPE_INTEGER, 10, NULL, true, false, 0);
    $dbman->add_field($table, $field);

    upgrade_plugin_savepoint(true, 2022112507, 'local', 'learningcompanions');
}
