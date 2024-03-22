<?php
defined('MOODLE_INTERNAL') || die();
global $DB;
if ($oldversion < 2022101300) {
    $dbman = $DB->get_manager();

// CREATE NEW TABLE thi_thi_lc_groups.
    $table = new xmldb_table('thi_thi_lc_groups');
// Add field id.
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', NULL, true, true, NULL);
// Add field name.
    $table->add_field('name', XMLDB_TYPE_CHAR, '100', NULL, true, false, NULL);
// Add field description.
    $table->add_field('description', XMLDB_TYPE_TEXT, '4000', NULL, true, false, NULL);
// Add field closedgroup.
    $table->add_field('closedgroup', XMLDB_TYPE_INTEGER, '1', NULL, true, false, '0');
// Add field cmid.
    $table->add_field('cmid', XMLDB_TYPE_INTEGER, '10', NULL, true, false, NULL);
// Add field createdby.
    $table->add_field('createdby', XMLDB_TYPE_INTEGER, '10', NULL, true, false, NULL);
// Add field timecreated.
    $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', NULL, true, false, NULL);
// Add field timemodified.
    $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', NULL, true, false, NULL);
// Add field courseid.
    $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', NULL, true, false, NULL);
// Add key primary.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }

// CREATE NEW TABLE thi_lc_keywords.
    $table = new xmldb_table('thi_lc_keywords');
// Add field id.
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', NULL, true, true, NULL);
// Add field keyword.
    $table->add_field('keyword', XMLDB_TYPE_CHAR, '60', NULL, true, false, NULL);
// Add key primary.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }

// CREATE NEW TABLE thi_thi_lc_groups_keywords.
    $table = new xmldb_table('thi_thi_lc_groups_keywords');
// Add field id.
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', NULL, true, true, NULL);
// Add field groupid.
    $table->add_field('groupid', XMLDB_TYPE_INTEGER, '10', NULL, true, false, NULL);
// Add field keywordid.
    $table->add_field('keywordid', XMLDB_TYPE_INTEGER, '10', NULL, true, false, NULL);
// Add key primary.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    $table->add_index('group_keyword', XMLDB_INDEX_UNIQUE, array('groupid', 'keywordid'));
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }

// CREATE NEW TABLE thi_lc_group_members.
    $table = new xmldb_table('thi_lc_group_members');
// Add field id.
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', NULL, true, true, NULL);
// Add field groupid.
    $table->add_field('groupid', XMLDB_TYPE_INTEGER, '10', NULL, true, false, NULL);
// Add field userid.
    $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', NULL, true, false, NULL);
// Add field joined.
    $table->add_field('joined', XMLDB_TYPE_INTEGER, '10', NULL, true, false, NULL);
// Add key primary.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    $table->add_index('groupid', XMLDB_INDEX_NOTUNIQUE, array('groupid'));
    $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));
    $table->add_index('group_user', XMLDB_INDEX_UNIQUE, array('groupid', 'userid'));
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }

    upgrade_plugin_savepoint(true, 2022101300, 'local', 'thi_learning_companions');
}