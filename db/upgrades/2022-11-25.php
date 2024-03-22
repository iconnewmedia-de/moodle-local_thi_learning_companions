<?php

defined('MOODLE_INTERNAL') || die();

global $CFG, $DB;

if ($oldversion < 2022112506) {
    require_once(__DIR__ . '/../lib.php');;
    local_thi_learning_companions\db\create_status_profile_field();

    upgrade_plugin_savepoint(true, 2022112506, 'local', 'thi_learning_companions');
}

if ($oldversion < 2022112507) {
    $dbman = $DB->get_manager();

    // MODIFY TABLE thi_lc_mentor_questions.
    $table = new xmldb_table('thi_lc_mentor_questions');
    // ------------ add field 'topic'
    $field = new xmldb_field('topic');
    $field->set_attributes(XMLDB_TYPE_INTEGER, 10, NULL, true, false, 0);
    $dbman->add_field($table, $field);

    upgrade_plugin_savepoint(true, 2022112507, 'local', 'thi_learning_companions');
}
