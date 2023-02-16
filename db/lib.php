<?php
namespace local_learningcompanions\db;
/**
 * creates course custom field "topic" which can be used to figure out which courses a mentor is responsible for
 * @return void
 */
function create_course_customfields() {
    $fieldCategoryExists = \core_customfield\category::get_record(array('name' => 'Learningcompanions'));
    if (!$fieldCategoryExists) {
        $context = \context_system::instance();
        $contextID = $context->instanceid;
        $category = (object)array('name' => 'Learningcompanions', 'component' => 'core_course', 'area' => 'course', 'contextid' => $contextID);
        $fieldCategory = new \core_customfield\category(0, $category);
        $fieldCategory->save();
        $fieldCategoryID = $fieldCategory->get('id');
    } else {
        $fieldCategoryID = $fieldCategoryExists->get('id');
    }

    $fieldExists = \core_customfield\field::get_record(array('shortname' => 'topic'));
    if (!$fieldExists) {
        $customField = array(
            'shortname' => 'topic',
            'name' => get_string('topic', 'local_learningcompanions'),
            'type' => 'text',
            'categoryid' => $fieldCategoryID,
            'description' => get_string('customfield_topic_description', 'local_learningcompanions'),
            'timecreated' => time(),
            'timemodified' => 0
        );
        $customField = (object)$customField;
        $field = new \core_customfield\field(0, $customField);
        $field->save();
    }
}