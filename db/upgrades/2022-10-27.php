<?php
defined('MOODLE_INTERNAL') || die();
global $DB;

if ($oldversion < 2022102801) {
    $dbman = $DB->get_manager();

    // ##################### CREATE NEW TABLE thi_lc_mentor_questions
    $table = new xmldb_table('thi_lc_mentor_questions');
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', NULL, true, true);
    $table->add_field('askedby', XMLDB_TYPE_INTEGER, '10', NULL, true, false);
    $table->add_field('mentorid', XMLDB_TYPE_INTEGER, '10', NULL, false, false);
    $table->add_field('question', XMLDB_TYPE_TEXT, '', NULL, true, false);
    $table->add_field('timeclosed', XMLDB_TYPE_INTEGER, '10', NULL, false, false);
    $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', NULL, true, false);
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }

    // ##################### CREATE NEW TABLE thi_lc_mentor_answers
    $table = new xmldb_table('thi_lc_mentor_answers');
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', NULL, true, true);
    $table->add_field('questionid', XMLDB_TYPE_INTEGER, '10', NULL, true, false);
    $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', NULL, true, false);
    $table->add_field('answer', XMLDB_TYPE_TEXT, '', NULL, true, false);
    $table->add_field('issolution', XMLDB_TYPE_INTEGER, '1', NULL, true, false, 0);
    $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', NULL, true, false);
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }

    upgrade_plugin_savepoint(true, 2022102801, 'local', 'thi_learning_companions');
}
