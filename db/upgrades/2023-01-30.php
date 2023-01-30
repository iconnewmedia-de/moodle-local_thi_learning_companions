<?php

defined('MOODLE_INTERNAL') || die();

global $CFG, $DB;

if ($oldversion < 2023013001) {
    $dbman = $DB->get_manager();

    $table = new xmldb_table('lc_chat');
    $dbman->drop_index($table, new xmldb_index('lcchat_cou_uix', XMLDB_INDEX_NOTUNIQUE, ['course']));

    upgrade_plugin_savepoint(true, 2023013001, 'local', 'learningcompanions');
}
