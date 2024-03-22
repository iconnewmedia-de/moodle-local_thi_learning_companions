<?php
defined('MOODLE_INTERNAL') || die();
global $DB;
if ($oldversion < 2022102000) {
    $dbman = $DB->get_manager();

// MODIFY TABLE thi_lc_chat_comment.
    $table = new xmldb_table('thi_lc_chat_comment');
// ------------ add field comment
    $field = new xmldb_field('comment');
    $field->set_attributes(XMLDB_TYPE_TEXT, NULL, NULL, true, false, NULL, NULL);
    $dbman->add_field($table, $field);

    upgrade_plugin_savepoint(true, 2022102000, 'local', 'thi_learning_companions');
}
