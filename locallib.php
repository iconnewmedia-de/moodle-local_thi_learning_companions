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

/**
 * Das Projekt THISuccessAI (FBM202-EA-1690-07540) wird im Rahmen der Förderlinie „Hochschulen durch Digitalisierung stärken“
 * durch die Stiftung Innovation in der Hochschulehre gefördert.
 *
 * @package     local_thi_learning_companions
 * @copyright   2022 ICON Vernetzte Kommunikation GmbH <info@iconnewmedia.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_thi_learning_companions;
/**
 * handles a chat submission (posting a new comment in the chat)
 * @param \stdClass $data
 * @return array|bool[]
 */
function chat_handle_submission($data) {
    global $DB;
    try {
        $transaction = $DB->start_delegated_transaction();
        $data->message = $data->message["text"];
        $attachmentsaved = \local_thi_learning_companions\chats::post_comment($data, chat_post_form::editor_options(0));
        $transaction->allow_commit();
        $return = ["success" => true];
        if (!$attachmentsaved) {
            $config = get_config('local_thi_learning_companions');
            $limit = intval($config->upload_limit_per_chat) . 'M';
            $return['warning_body'] = get_string('attachment_chat_filesize_excdeeded', 'local_thi_learning_companions', $limit);
            $return['warning_title'] = get_string('warning', 'local_thi_learning_companions', $limit);
        }
        return $return;
    } catch (\Exception $e) {
        try {
            $transaction->rollback($e);
        } catch (\file_exception $e) {
            return ["success" => false, "error" => $e->getMessage()];
        }
    }
}

/**
 * returns the topics of a course
 * @param $courseid
 * @return string[]
 * @throws \dml_exception
 */
function get_course_topics($courseid) {
    global $DB;
    $records = $DB->get_records_sql(
        "SELECT DISTINCT cd.value
        FROM {customfield_data} cd
        JOIN {customfield_field} cf ON cd.fieldid = cf.id AND cf.shortname = 'topic'
        JOIN {customfield_category} cg ON cg.id = cf.categoryid AND cg.name = 'thi_learning_companions'
        JOIN {context} ctx ON ctx.id = cd.contextid AND ctx.contextlevel = '" . CONTEXT_COURSE . "' AND ctx.instanceid = ?",
    [$courseid]
    );
    $topics = array_keys($records);
    if (count($topics) === 1 && empty(trim($topics[0]))) {
        return null;
    }
    return $topics;
}

/**
 * returns all topics of a user's courses
 * @param int $userid
 * @return string[]
 * @throws \coding_exception
 * @throws \dml_exception
 */
function get_topics_of_user_courses(int|null $userid = null) {
    global $DB, $USER;
    if (is_null($userid) && isloggedin()) {
        $userid = $USER->id;
    } else if (!isloggedin()) {
        return [];
    }
    $userenrolments = enrol_get_all_users_courses($userid);
    $userenrolments = array_keys($userenrolments);
    if (empty($userenrolments)) {
        return [];
    }
    list($coursecondition, $courseparams) = $DB->get_in_or_equal($userenrolments);
    $records = $DB->get_records_sql(
        "SELECT DISTINCT cd.value
        FROM {customfield_data} cd
        JOIN {customfield_field} cf ON cd.fieldid = cf.id AND cf.shortname = 'topic'
        JOIN {customfield_category} cg ON cg.id = cf.categoryid AND cg.name = 'thi_learning_companions'
        JOIN {context} ctx
            ON ctx.id = cd.contextid
            AND ctx.contextlevel = '" . CONTEXT_COURSE . "'
            AND ctx.instanceid " . $coursecondition,
        $courseparams
    );
    $topics = [];
    foreach ($records as $key => $value) {
        $key = trim($key);
        if (!empty($key)) {
            $topics[] = $key;
        }
    }
    return $topics;
}

/**
 * invites users to a group
 * @return void
 * @throws \coding_exception
 */
function invite_users() {
    global $OUTPUT;
    $groupid = required_param('groupid', PARAM_INT);
    $userlist = required_param_array('userlist', PARAM_INT);
    if (empty($userlist)) {
        return;
    }
    groups::invite_users_to_group($userlist, $groupid);
    $notification = $OUTPUT->render(
        new \core\output\notification(
            get_string('users_invited', 'local_thi_learning_companions'),
            \core\output\notification::NOTIFY_SUCCESS)
    );
    echo $notification;
}

/**
 * returns the course module types that should have a comment block added
 * @return false|string[]
 * @throws \dml_exception
 */
function get_moduletypes_for_commentblock() {
    $config = get_config('local_thi_learning_companions');
    $whitelist = explode(',', $config->commentactivities);
    array_walk($whitelist, 'trim');
    return $whitelist;
}

/**
 * creates a comment block
 * @param $parentcontextid
 * @param $modulename
 * @return void
 * @throws \dml_exception
 */
function create_comment_block($parentcontextid, $modulename) {
    global $DB;
    $block = new \stdClass();
    $block->blockname = 'comments';
    $block->parentcontextid = $parentcontextid;
    $block->showinsubcontexts = '';
    $block->pagetypepattern = 'mod-' . $modulename . '-*';
    $block->subpagepattern = '';
    $block->defaultregion = 'side-pre';
    $block->defaultweight = '2';
    $block->configdata = '';
    $block->timecreated = time();
    $block->timemodified = time();

    $DB->insert_record('block_instances', $block);
}

/**
 * adds comment blocks
 * @return void
 * @throws \coding_exception
 * @throws \dml_exception
 */
function add_comment_blocks() {
    global $DB;
    $whitelist = get_moduletypes_for_commentblock();
    list($sqlwherein, $params) = $DB->get_in_or_equal($whitelist);
    $activitieswithoutcommentblock = $DB->get_records_sql(
        "SELECT ctx.id as contextid, m.name as modulename
                FROM {context} ctx
                JOIN {course_modules} cm ON cm.id = ctx.instanceid
                JOIN {modules} m ON m.id = cm.module
                LEFT JOIN {block_instances} bi ON ctx.id = bi.parentcontextid AND bi.blockname = 'comments'
                WHERE bi.id IS NULL
                AND ctx.contextlevel = 70
                AND m.name " . $sqlwherein,
        $params
    );
    foreach ($activitieswithoutcommentblock as $activity) {
        create_comment_block($activity->contextid, $activity->modulename);
    }
}

/**
 * creates course custom field "topic" which can be used to figure out which courses a mentor is responsible for
 * @return void
 */
function create_course_customfields() {
    $fieldcategoryexists = \core_customfield\category::get_record(['name' => 'thi_learning_companions']);
    if (!$fieldcategoryexists) {
        $context = \context_system::instance();
        $contextid = $context->instanceid;
        $category = (object)['name' => 'thi_learning_companions',
            'component' => 'core_course',
            'area' => 'course',
            'contextid' => $contextid,
        ];
        $fieldcategory = new \core_customfield\category(0, $category);
        $fieldcategory->save();
        $fieldcategoryid = $fieldcategory->get('id');
    } else {
        $fieldcategoryid = $fieldcategoryexists->get('id');
    }

    $fieldexists = \core_customfield\field::get_record(['shortname' => 'topic']);
    if (!$fieldexists) {
        $customfield = [
            'shortname' => 'topic',
            'name' => get_string('topic', 'local_thi_learning_companions'),
            'type' => 'text',
            'categoryid' => $fieldcategoryid,
            'description' => get_string('customfield_topic_description', 'local_thi_learning_companions'),
            'timecreated' => time(),
            'timemodified' => 0,
        ];
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
        'name' => get_string('profile_field_category_status_default', 'local_thi_learning_companions'),
    ]);
    if (!$categoryid) {
        $categoryid = $DB->insert_record('user_info_category', [
            'name' => get_string('profile_field_category_status_default', 'local_thi_learning_companions'),
            'sortorder' => 1,
        ]);
    }

    /*** Adding new profile field 'lc_user_status' ***/
    require_once($CFG->dirroot.'/user/profile/definelib.php');
    require_once($CFG->dirroot.'/user/profile/field/menu/define.class.php');
    $newfield = $DB->get_record('user_info_field', ['shortname' => 'lc_user_status']);
    $fieldtype = new \profile_define_menu();
    if ($newfield) {
        // Update default data and param1 if the field already exists, we've got new default values.
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

