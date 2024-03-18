<?php
defined('MOODLE_INTERNAL') || die();
global $DB;
if ($oldversion < 2022112103) {
    $dbman = $DB->get_manager();

// ##################### MODIFY TABLE thi_lc_mentor_questions
    $table = new xmldb_table('thi_lc_mentor_questions');
// ------------ add field 'title'
    $field = new xmldb_field('title');
    $field->set_attributes(XMLDB_TYPE_TEXT, '', NULL, false, false);
    $dbman->add_field($table, $field);

    upgrade_plugin_savepoint(true, 2022112103, 'local', 'thi_learning_companions');
}
