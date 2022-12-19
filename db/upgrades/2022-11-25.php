<?php

defined('MOODLE_INTERNAL') || die();

global $CFG, $DB;

if ($oldversion < 2022112506) {
    /*** Adding new profile category 'Status' ***/
    $categoryId = $DB->insert_record('user_info_category', [
        'name' => get_string('profile_field_category_status_default'),
        'sortorder' => 1
    ]);

    /*** Adding new profile field 'lc_user_status' ***/

    require_once($CFG->dirroot.'/user/profile/definelib.php');
    require_once($CFG->dirroot.'/user/profile/field/menu/define.class.php');
    $fieldtype = new profile_define_menu();

    $newfield = new stdClass();
    $newfield->shortname = 'lc_user_status';
    $newfield->name = 'Learning companions user status';
    $newfield->datatype = 'menu';
    $newfield->description = '';
    $newfield->required = 0;
    $newfield->locked = 0;
    $newfield->forceunique = 0;
    $newfield->signup = 0;
    $newfield->visible = 2;
    $newfield->categoryid = $categoryId;
    // Multi language, take a look at the strings.
    $newfield->defaultdata = get_string('profile_field_status_default_default', 'local_learningcompanions');
    $newfield->param1 = get_string('profile_field_status_default_options', 'local_learningcompanions');

    $fieldtype->define_save($newfield);
    profile_reorder_fields();
    profile_reorder_categories();

    // Active and configure filter for multi language.
    filter_set_global_state('multilang', TEXTFILTER_ON);
    $stringfilters = $CFG->stringfilters.',multilang';
    set_config('stringfilters', $stringfilters);

    upgrade_plugin_savepoint(true, 2022112506, 'local', 'learningcompanions');
}

if ($oldversion < 2022112507) {
    $dbman = $DB->get_manager();

    // ##################### MODIFY TABLE lc_mentor_questions
    $table = new xmldb_table('lc_mentor_questions');
    // ------------ add field 'topic'
    $field = new xmldb_field('topic');
    $field->set_attributes(XMLDB_TYPE_INTEGER, 10, NULL, true, false, 0);
    $dbman->add_field($table, $field);

    upgrade_plugin_savepoint(true, 2022112507, 'local', 'learningcompanions');
}
