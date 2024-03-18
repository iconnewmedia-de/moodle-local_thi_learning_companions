<?php
defined('MOODLE_INTERNAL') || die();

global $CFG, $DB;

if ($oldversion < 2023022800) {
    require_once __DIR__ . '/../lib.php';
    $dbman = $DB->get_manager();
    // Define field id to be added to lc_chat_comment_ratings.
    $table = new xmldb_table('lc_tutor_notifications');
    if (!$dbman->table_exists($table)) {
        $field = new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $table->addField($field);
        $field = new xmldb_field('questionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'id');
        $table->addField($field);
        $field = new xmldb_field('tutorid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'questionid');
        $table->addField($field);
        $field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'tutorid');
        $table->addField($field);
        $key = new xmldb_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->addKey($key);
        $index = new xmldb_index('questionid', XMLDB_INDEX_NOTUNIQUE, ['questionid']);
        $table->addIndex($index);
        $dbman->create_table($table);
    }

    upgrade_plugin_savepoint(true, 2023022800, 'local', 'thi_learning_companions');
}
