<?php
if ($oldversion < 2022112900) {
    $dbman = $DB->get_manager();
// ##################### CREATE NEW TABLE lc_chat_lastvisited
    $table = new xmldb_table('lc_chat_lastvisited');
//----------------- add field id
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', NULL, true, true, NULL);
//----------------- add field userid
    $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', NULL, true, false, NULL);
//----------------- add field chatid
    $table->add_field('chatid', XMLDB_TYPE_INTEGER, '10', NULL, true, false, NULL);
//----------------- add field timevisited
    $table->add_field('timevisited', XMLDB_TYPE_INTEGER, '10', NULL, true, false, NULL);
//-------------add key primary
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    $table->add_index('chatid', XMLDB_INDEX_NOTUNIQUE, array('chatid'));
    $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));
    $table->add_index('chatid_userid', XMLDB_INDEX_UNIQUE, array('userid,chatid'));
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }
    upgrade_plugin_savepoint(true, 2022112900, 'local', 'learningcompanions');

}

if ($oldversion < 2022112902) {
    // ##################### MODIFY TABLE lc_groups
    $table = new xmldb_table('lc_groups');
// ------------ add field latestcomment
    $field = new xmldb_field('latestcomment');
    $field->set_attributes(XMLDB_TYPE_INTEGER, '10', NULL, false, false, '0', NULL);
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }
    upgrade_plugin_savepoint(true, 2022112902, 'local', 'learningcompanions');

}