<?php
defined('MOODLE_INTERNAL') || die();
global $DB;
if ($oldversion < 2022101900) {
    $dbman = $DB->get_manager();

// CREATE NEW TABLE thi_lc_mentors.
    $table = new xmldb_table('thi_lc_mentors');
// Add field id.
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', NULL, true, true, NULL);
// Add field userid.
    $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', NULL, true, false, NULL);
// Add field topic.
    $table->add_field('topic', XMLDB_TYPE_CHAR, '255', NULL, false, false, NULL);
// Add key primary.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }
    
// CREATE NEW TABLE thi_lc_users_mentors.
    $table = new xmldb_table('thi_lc_users_mentors');
// Add field id.
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', NULL, true, true, NULL);
// Add field userid.
    $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', NULL, true, false, NULL);
// Add field mentorid.
    $table->add_field('mentorid', XMLDB_TYPE_INTEGER, '10', NULL, false, false, NULL);
// Add key primary.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
// Add key fk_mentorid.
    $table->add_key('fk_mentorid', XMLDB_KEY_FOREIGN, array('mentorid'));
    $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));
    $table->add_index('useridmentor', XMLDB_INDEX_NOTUNIQUE, array('userid'));
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }

// CREATE NEW TABLE thi_lc_chat.
    $table = new xmldb_table('thi_lc_chat');
// Add field id.
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', NULL, true, true, NULL);
// Add field course.
    $table->add_field('course', XMLDB_TYPE_INTEGER, '10', NULL, true, false, NULL);
// Add field chattype.
    $table->add_field('chattype', XMLDB_TYPE_INTEGER, '2', NULL, true, false, '0');
// Add field relatedid.
    $table->add_field('relatedid', XMLDB_TYPE_INTEGER, '10', NULL, true, false, NULL);
// Add field timecreated.
    $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', NULL, false, false, NULL);
// Add key primary.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    $table->add_index('chattype_relatedid', XMLDB_INDEX_UNIQUE, array('chattype,relatedid'));
    $table->add_index('course', XMLDB_INDEX_NOTUNIQUE, array('course'));
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }
    
// CREATE NEW TABLE thi_lc_chat_comment.
    $table = new xmldb_table('thi_lc_chat_comment');
// Add field id.
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', NULL, true, true, NULL);
// Add field chatid.
    $table->add_field('chatid', XMLDB_TYPE_INTEGER, '10', NULL, true, false, NULL);
// Add field userid.
    $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', NULL, true, false, NULL);
// Add field flagged.
    $table->add_field('flagged', XMLDB_TYPE_INTEGER, '1', NULL, true, false, '0');
// Add field totalscore.
    $table->add_field('totalscore', XMLDB_TYPE_INTEGER, '5', NULL, true, false, '0');
// Add field timecreated.
    $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', NULL, true, false, NULL);
// Add field timemodified.
    $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', NULL, false, false, NULL);
// Add key primary.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    $table->add_index('chatid', XMLDB_INDEX_NOTUNIQUE, array('chatid'));
    $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }
    upgrade_plugin_savepoint(true, 2022101900, 'local', 'thi_learning_companions');
}
