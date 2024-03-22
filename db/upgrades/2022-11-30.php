<?php

defined('MOODLE_INTERNAL') || die();

global $CFG, $DB;

if ($oldversion < 2022113000) {
    $dbman = $DB->get_manager();

    $table = new xmldb_table('lc_group_requests');
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', NULL, true, true);
    $table->add_field('groupid', XMLDB_TYPE_INTEGER, '10', NULL, true, false);
    $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', NULL, true, false);
    $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', NULL, true, false);
    $table->add_field('denied', XMLDB_TYPE_INTEGER, '1', NULL, true, false, 0);

    // Add key primary.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
    $table->add_index('groupid', XMLDB_INDEX_NOTUNIQUE, ['groupid']);
    $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, ['userid']);
    $table->add_index('group_user', XMLDB_INDEX_UNIQUE, ['groupid', 'userid']);

    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }

    upgrade_plugin_savepoint(true, 2022113000, 'local', 'thi_learning_companions');
}
