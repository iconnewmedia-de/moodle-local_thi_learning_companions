<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
namespace local_thi_learning_companions\db;
/**
 * creates course custom field "topic" which can be used to figure out which courses a mentor is responsible for
 * @return void
 */
function create_course_customfields() {
    $fieldcategoryexists = \core_customfield\category::get_record(array('name' => 'thi_learning_companions'));
    if (!$fieldcategoryexists) {
        $context = \context_system::instance();
        $contextid = $context->instanceid;
        $category = (object)array('name' => 'thi_learning_companions', 'component' => 'core_course', 'area' => 'course', 'contextid' => $contextid);
        $fieldcategory = new \core_customfield\category(0, $category);
        $fieldcategory->save();
        $fieldcategoryid = $fieldcategory->get('id');
    } else {
        $fieldcategoryid = $fieldcategoryexists->get('id');
    }

    $fieldexists = \core_customfield\field::get_record(array('shortname' => 'topic'));
    if (!$fieldexists) {
        $customfield = array(
            'shortname' => 'topic',
            'name' => get_string('topic', 'local_thi_learning_companions'),
            'type' => 'text',
            'categoryid' => $fieldcategoryid,
            'description' => get_string('customfield_topic_description', 'local_thi_learning_companions'),
            'timecreated' => time(),
            'timemodified' => 0
        );
        $customfield = (object)$customfield;
        $field = new \core_customfield\field(0, $customfield);
        $field->save();
    }
}

/**
 * creates a new profile field to hold the user status
 * @return void
 * @throws \coding_exception
 * @throws \dml_exception
 */
function create_status_profile_field() {
    global $DB, $CFG;
    /*** Adding new profile category 'Status' ***/
    $categoryid = $DB->get_field('user_info_category', 'id', [
        'name' => get_string('profile_field_category_status_default', 'local_thi_learning_companions')
    ]);
    if (!$categoryid) {
        $categoryid = $DB->insert_record('user_info_category', [
            'name' => get_string('profile_field_category_status_default', 'local_thi_learning_companions'),
            'sortorder' => 1
        ]);
    }

    /*** Adding new profile field 'lc_user_status' ***/
    require_once($CFG->dirroot.'/user/profile/definelib.php');
    require_once($CFG->dirroot.'/user/profile/field/menu/define.class.php');
    $newfield = $DB->get_record('user_info_field', array('shortname' => 'lc_user_status'));
    $fieldtype = new \profile_define_menu();
    if ($newfield) {
        // update default data and param1 if the field already exists, we've got new default values
        $newfield->defaultdata = get_string('profile_field_status_default_default', 'local_thi_learning_companions');
        $newfield->param1 = get_string('profile_field_status_default_options', 'local_thi_learning_companions');
    } else {
        $newfield = new \stdClass();
        $newfield->shortname = 'lc_user_status';
        $newfield->name = 'Learning companions user status';
        $newfield->datatype = 'menu';
        $newfield->description = '';
        $newfield->required = 0;
        $newfield->locked = 0;
        $newfield->forceunique = 0;
        $newfield->signup = 0;
        $newfield->visible = 2;
        $newfield->categoryid = $categoryid;
        // Multi language, take a look at the strings.
        $newfield->defaultdata = get_string('profile_field_status_default_default', 'local_thi_learning_companions');
        $newfield->param1 = get_string('profile_field_status_default_options', 'local_thi_learning_companions');
    }
    $fieldtype->define_save($newfield);
    profile_reorder_fields();
    profile_reorder_categories();

    // Active and configure filter for multi language.
    filter_set_global_state('multilang', TEXTFILTER_ON);
    $stringfilters = $CFG->stringfilters.',multilang';
    set_config('stringfilters', $stringfilters);
}
