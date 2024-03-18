<?php
defined('MOODLE_INTERNAL') || die();

global $CFG, $DB;
if ($oldversion < 2023022200) {
    $dbman = $DB->get_manager();
    // Define field id to be added to lc_chat_comment_ratings.
    $table = new xmldb_table('lc_chat_comment_ratings');
    if (!$dbman->table_exists($table)) {
        $field = new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $table->addField($field);
        $field = new xmldb_field('commentid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'id');
        $table->addField($field);
        $field = new xmldb_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'commentid');
        $table->addField($field);
        $key = new xmldb_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->addKey($key);
        $index = new xmldb_index('commentid_userid', XMLDB_INDEX_UNIQUE, ['commentid', 'userid']);
        $table->addIndex($index);
        $dbman->create_table($table);
    }
    // thi_learning_companions savepoint reached.
    upgrade_plugin_savepoint(true, 2023022200, 'local', 'thi_learning_companions');
}
