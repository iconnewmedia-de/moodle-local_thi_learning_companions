<?php

defined('MOODLE_INTERNAL') || die();

global $CFG, $DB;

if ($oldversion < 2023013001) {
    $dbman = $DB->get_manager();

    $table = new xmldb_table('thi_lc_chat');
    $dbman->drop_index($table, new xmldb_index('lcchat_cou_uix', XMLDB_INDEX_NOTUNIQUE, ['course']));

    upgrade_plugin_savepoint(true, 2023013001, 'local', 'thi_learning_companions');
}

if ($oldversion < 2023013002) {
    $dbman = $DB->get_manager();

    $table = new xmldb_table('thi_lc_chat_comment');
    $dbman->drop_index($table, new xmldb_index('lcchatcomm_use_uix', XMLDB_INDEX_NOTUNIQUE, ['userid']));
    $dbman->drop_index($table, new xmldb_index('lcchatcomm_cha_uix', XMLDB_INDEX_NOTUNIQUE, ['chatid']));

    upgrade_plugin_savepoint(true, 2023013002, 'local', 'thi_learning_companions');
}
