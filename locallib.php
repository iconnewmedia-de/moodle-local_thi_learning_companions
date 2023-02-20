<?php
namespace local_learningcompanions;

/**
 * @param $data
 * @param $form
 * @return array|bool[]
 */
function chat_handle_submission($data, $form) {
    require_once __DIR__ . '/classes/chat_post_form.php';
    require_once __DIR__ . '/classes/chat.php';
    try {
        $data->message = $data->message["text"];
        \local_learningcompanions\chats::post_comment($data, $form, chat_post_form::editor_options(0));
        return ["success" => true];
    } catch(\Exception $e) {
        return ["success" => false, "error" => $e->getMessage()];
    }
}

function get_course_topics($courseid) {
    global $DB;
    $records = $DB->get_records_sql(
        "SELECT DISTINCT cd.value
        FROM {customfield_data} cd
        JOIN {customfield_field} cf ON cd.fieldid = cf.id AND cf.shortname = 'topic'
        JOIN {customfield_category} cg ON cg.id = cf.categoryid AND cg.name = 'Learningcompanions'
        JOIN {context} ctx ON ctx.id = cd.contextid AND ctx.contextlevel = '" . CONTEXT_COURSE . "' AND ctx.instanceid = ?",
    array($courseid)
    );
    $topics = array_keys($records);
    return $topics;
}

/**
 * returns all topics of a user's courses
 * @param int $userid
 * @return string[]
 * @throws \coding_exception
 * @throws \dml_exception
 */
function get_topics_of_user_courses(int $userid = null) {
    global $DB, $USER;
    if (is_null($userid) && isloggedin()) {
        $userid = $USER->id;
    } elseif(!isloggedin()) {
        return [];
    }
    $userEnrolments = enrol_get_all_users_courses($userid);
    $userEnrolments = array_keys($userEnrolments);
    if (empty($userEnrolments)) {
        return [];
    }
    list($courseCondition, $courseParams) = $DB->get_in_or_equal($userEnrolments);
    $records = $DB->get_records_sql(
        "SELECT DISTINCT cd.value
        FROM {customfield_data} cd
        JOIN {customfield_field} cf ON cd.fieldid = cf.id AND cf.shortname = 'topic'
        JOIN {customfield_category} cg ON cg.id = cf.categoryid AND cg.name = 'Learningcompanions'
        JOIN {context} ctx 
            ON ctx.id = cd.contextid
            AND ctx.contextlevel = '" . CONTEXT_COURSE . "'
            AND ctx.instanceid " . $courseCondition,
        $courseParams
    );
    return array_keys($records);
}