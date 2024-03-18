<?php
defined('MOODLE_INTERNAL') || die();

global $CFG, $DB;

if ($oldversion < 2023020201) {
    $dbman = $DB->get_manager();
    // Define table lc_bbb to be created.
    $table = new xmldb_table('lc_bbb');

    // Adding fields to table lc_bbb.
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('type', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
    $table->add_field('intro', XMLDB_TYPE_TEXT, null, null, null, null, null);
    $table->add_field('introformat', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '1');
    $table->add_field('meetingid', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
    $table->add_field('moderatorpass', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
    $table->add_field('viewerpass', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
    $table->add_field('wait', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('record', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('recordallfromstart', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('recordhidebutton', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('welcome', XMLDB_TYPE_TEXT, null, null, null, null, null);
    $table->add_field('voicebridge', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('openingtime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('closingtime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('presentation', XMLDB_TYPE_TEXT, null, null, null, null, null);
    $table->add_field('participants', XMLDB_TYPE_TEXT, null, null, null, null, null);
    $table->add_field('userlimit', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('recordings_html', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('recordings_deleted', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
    $table->add_field('recordings_imported', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('recordings_preview', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('clienttype', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('muteonstart', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('disablecam', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('disablemic', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('disableprivatechat', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('disablepublicchat', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('disablenote', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('hideuserlist', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('lockedlayout', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('lockonjoin', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('lockonjoinconfigurable', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('completionattendance', XMLDB_TYPE_INTEGER, '9', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('completionengagementchats', XMLDB_TYPE_INTEGER, '9', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('completionengagementtalks', XMLDB_TYPE_INTEGER, '9', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('completionengagementraisehand', XMLDB_TYPE_INTEGER, '9', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('completionengagementpollvotes', XMLDB_TYPE_INTEGER, '9', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('completionengagementemojis', XMLDB_TYPE_INTEGER, '9', null, XMLDB_NOTNULL, null, '0');

    // Adding keys to table lc_bbb.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

    // Conditionally launch create table for lc_bbb.
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }

    upgrade_plugin_savepoint(true, 2023020201, 'local', 'thi_learning_companions');
}
