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
namespace local_thi_learning_companions;
use local_thi_learning_companions\event\mentor_assigned;
use local_thi_learning_companions\event\question_created;
defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . "/../locallib.php");
require_once($CFG->libdir . "/badgeslib.php");
require_once($CFG->dirroot . "/badges/classes/badge.php");
class mentors {

    /**
     * @param $topic
     * @param $supermentorsonly
     * @param bool $excludecurrentuser allows to exclude the current user from the mentor search
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function get_mentors($topic = null, $supermentorsonly = false, $excludecurrentuser = false): array {
        global $CFG, $DB, $OUTPUT, $USER;
        // ICTODO: There is definitely a better way to query this.
        // ICTODO: Maybe split into basic and extended function.

        require_once($CFG->dirroot.'/local/thi_learning_companions/lib.php');

        $sql = 'SELECT DISTINCT m.userid, GROUP_CONCAT(m.topic) as topics,
                       u.*
                  FROM {thi_lc_mentors} m
             LEFT JOIN {user} u ON u.id = m.userid
             ';
        $params = [];
        $conditions = [];
        if (!is_null($topic)) {
            $conditions[] = ' m.topic = ?';
            $params[] = $topic;
        }

        if ($excludecurrentuser) {
            $conditions[] = ' m.userid <> ?';
            $params[] = $USER->id;
        }
        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $sql .= ' GROUP BY u.id';
        $mentors = $DB->get_records_sql($sql, $params);

        $context = \context_system::instance();
        foreach ($mentors as $mentor) {
            $mentor->issupermentor = self::is_supermentor($mentor->userid);
            if ($supermentorsonly && !$mentor->issupermentor) {
                unset($mentors[$mentor->userid]);
            } else {
                $mentor->fullname = fullname($mentor);
                $mentor->profileurl = $CFG->wwwroot.'/user/profile.php?id='.$mentor->userid;
                $mentor->userpic = $OUTPUT->user_picture($mentor, [
                    'link' => false, 'visibletoscreenreaders' => false,
                    'class' => 'userpicture',
                ]);
                list($mentor->status, $mentor->statustext) = local_thi_learning_companions_get_user_status($mentor->userid);
                $mentor->badges = badges_get_user_badges($mentor->id, 0, 0, 0, '', true);
                $mentor->badges = array_values($mentor->badges);
                foreach ($mentor->badges as $badge) {
                    $badgeobj = new \badge($badge->id);
                    if (!is_null($badge->courseid)) {
                        $badgecontext = \context_course::instance($badge->courseid);
                    } else {
                        $badgecontext = $context;
                    }
                    $badge->image = print_badge_image($badgeobj, $badgecontext);
                }
                $topiclist = [];
                $topics = explode(',', $mentor->topics);
                foreach ($topics as $mentortopic) {
                    $topiclist[] = ['topic' => trim($mentortopic)];
                }
                $mentor->topiclist = $topiclist;
            }
        }

        return $mentors;
    }

    public static function get_my_mentors() {

    }

    public static function may_become_mentor() {

    }

    /**
     * @param $userid
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function is_mentor($userid = null) {
        global $USER;
        $context = \context_system::instance();
        $userid = is_null($userid) ? $USER->id : $userid;
        return has_capability(
            'local/thi_learning_companions:mentor_ismentor',
            $context,
            $userid
        ); // Maybe access restriction by database entry.
    }

    /**
     * returns an array with all badge names that are available within a given set of mentors
     * @param $mentors
     * @return array
     */
    public static function get_selectable_badgetypes($mentors) {
        $uniquebadgenames = [];
        foreach ($mentors as $mentor) {
            foreach ($mentor->badges as $badge) {
                $uniquebadgenames[] = $badge->name;
            }
        }
        $uniquebadgenames = array_unique($uniquebadgenames);
        return $uniquebadgenames;
    }

    /**
     * @param $userid
     * @param $topic
     * @return bool
     * @throws \dml_exception
     */
    public static function is_mentor_for_topic($userid, $topic) {
        global $DB;
        return $DB->record_exists('lc_mentor', ['userid' => $userid, 'topic' => $topic]);
    }

    /**
     * @param $userid
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function is_supermentor($userid = null) {
        global $USER, $DB;
        $userid = is_null($userid) ? $USER->id : $userid;
        $config = get_config('local_thi_learning_companions');
        $minimumratings = intval($config->supermentor_minimum_ratings);
        $countmentorratings = self::count_mentor_ratings($userid);
        return $countmentorratings >= $minimumratings;
    }

    public static function count_mentor_ratings($userid = null) {
        global $USER, $DB;
        $userid = is_null($userid) ? $USER->id : $userid;
        $countmentorratings = $DB->count_records_sql(
            "SELECT count(distinct cr.id)
                    FROM {thi_lc_chat_comment} c
                     JOIN {thi_lc_chat_comment_ratings} cr ON cr.commentid = c.id
                     WHERE c.userid = ?",
            [$userid]
        );
        return $countmentorratings;
    }

    /**
     * @param $userid
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function is_tutor($userid = null) {
        global $USER;
        $context = \context_system::instance();
        $userid = is_null($userid) ? $USER->id : $userid;
        return has_capability(
            'local/thi_learning_companions:mentor_istutor',
            $context,
            $userid
        ); // Maybe access restriction by database entry or teacher role.
    }

    /**
     * @param int  $userid
     * @return question[]
     * @throws \dml_exception
     */
    public static function get_my_asked_questions(int $userid): array {
        return question::get_all_questions_for_user($userid);
    }

    /**
     * @param int $userid
     *
     * @return array
     * @throws \dml_exception
     */
    public static function get_mentor_questions_by_user_id(int $userid): array {
        return question::get_all_questions_for_mentor_user($userid);
    }

    /**
     * @param int[] $topics
     *
     * @return array
     */
    public static function get_open_mentor_questions_by_topics(array $topics): array {
        return question::get_all_questions_by_topics($topics, true);
    }

    /**
     * @param int|null   $userid
     * @param array|null $topics
     * @param bool       $onlyopen
     * @param bool       $extended
     * @return array
     * @throws \dml_exception
     */
    public static function get_all_mentor_questions(
        int $userid = null,
        array $topics = null,
        bool $onlyopen = false,
        bool $extended = false
    ): array {
        global $DB;

        if ($extended) {
            $sql = 'SELECT q.*,
                           FROM_UNIXTIME(q.timecreated, "%d.%m.%Y") AS dateasked,
                           IF(q.timeclosed=0, "-", FROM_UNIXTIME(q.timeclosed, "%d.%m.%Y - %H:%i")) AS dateclosed,
                           (SELECT COUNT(a.id) FROM {thi_lc_mentor_answers} a WHERE a.questionid = q.id) answercount,
                           (SELECT FROM_UNIXTIME(MAX(a.timecreated), "%d.%m.%Y")
                                FROM {thi_lc_mentor_answers} a
                                WHERE a.questionid = q.id
                            ) lastactivity
                      FROM {thi_lc_mentor_questions} q';
            $params = [];
            $conditions = 0;

            if (!is_null($userid)) {
                $sql .= ($conditions < 1) ? ' WHERE q.mentorid = ?' : ' AND q.mentorid = ?';
                $params[] = $userid;
                $conditions++;
            } else {
                $sql .= ($conditions < 1) ? ' WHERE q.mentorid IS NULL' : ' AND q.mentorid IS NULL';
                $conditions++;
            }

            if ($onlyopen) {
                $sql .= ($conditions) < 1 ? ' WHERE q.timeclosed IS NULL' : ' AND q.timeclosed IS NULL';
                $conditions++;
            }

            if (is_array($topics) && count($topics) > 0) {
                list($conditiontopics, $paramstopics) = $DB->get_in_or_equal($topics);
                $sql .= ($conditions < 1) ? ' WHERE q.topic ' . $conditiontopics : ' AND q.topic ' . $conditiontopics;
                $params = $params + $paramstopics;
                $conditions++;
            }

            return $DB->get_records_sql($sql, $params);
        }

        $params = [];
        if (!is_null($userid)) {
            $params['mentorid'] = $userid;
        }
        $questions = $DB->get_records('thi_lc_mentor_questions', $params);
        if ($onlyopen) {
            $questions = array_filter($questions, function($question) {
                return is_null($question->timeclosed);
            });
        }

        return $questions;
    }

    /**
     * @param $questionid
     * @return bool
     * @throws \dml_exception
     */
    public static function delete_asked_question($questionid): bool {
        global $DB;
        if (!self::may_user_delete_question($questionid)) {
            throw new \moodle_exception('no_permission_to_delete_question', 'local_thi_learning_companions');
        }
        $chatid = $DB->get_field('thi_lc_chat', 'id', ['relatedid' => $questionid, 'chattype' => groups::CHATTYPE_MENTOR]);
        return $DB->delete_records('thi_lc_chat_comment', ['chatid' => $chatid])
            && $DB->delete_records('thi_lc_mentor_questions', ['id' => $questionid]);
    }

    /**
     * @param $questionid
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    protected static function may_user_delete_question($questionid): bool {
        global $DB, $USER;
        $question = $DB->get_record('thi_lc_mentor_questions', ['id' => $questionid]);
        if ($question->askedby == $USER->id) {
            return true;
        }
        $context = \context_system::instance();
        if (has_capability('local/thi_learning_companions:delete_comments_of_others', $context)) {
            return true;
        }
        return false;
    }

    /**
     * @param $userid
     * @param $idsonly
     * @return array
     * @throws \dml_exception
     */
    public static function get_all_mentor_keywords($userid, $idsonly = false): array {
        global $DB;

        // ICTODO: Maybe we could check if given user is a mentor,
        // but in worst case we get an empty array.

        $sql = 'SELECT m.topic,
                       k.keyword
                  FROM {thi_lc_mentors} m
             LEFT JOIN {thi_lc_keywords} k ON k.id = m.topic
                 WHERE m.userid = ?';

        $keywords = $DB->get_records_sql($sql, [$userid]);

        if ($idsonly) {
            $keywordlist = [];
            foreach ($keywords as $keyword) {
                $keywordlist[] = $keyword->topic;
            }
            return $keywordlist;
        }

        return $keywords;
    }

    /**
     * @param $userid
     * @return false|mixed
     * @throws \dml_exception
     */
    public static function get_all_mentor_topics($userid = null) {
        global $USER, $DB;
        if (is_null($userid)) {
            $userid = $USER->id;
        }
        $topics = $DB->get_records('thi_lc_mentors', ['userid' => $userid], 'topic', 'topic');
        return array_keys($topics);
    }

    /**
     * returns an array of courses for which the user is qualified to become a mentor but hasn't become yet
     * @return array
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function get_new_mentorship_qualifications($userid = null) {
        global $USER, $DB, $CFG;
        if (is_null($userid)) {
            $userid = $USER->id;
        }
        $mentortopics = self::get_mentorship_topics($userid);
        $badgetopics = self::get_all_mentorship_qualifications($userid);
        $newqualifications = array_diff($badgetopics, $mentortopics);
        return $newqualifications;
    }

    /**
     * returns true if a given user either is mentor or has the qualification to become one
     * @param $userid
     * @return bool
     * @throws \dml_exception
     */
    public static function is_qualified_as_mentor($userid = null) {
        global $CFG, $USER;
        if (is_null($userid)) {
            $userid = $USER->id;
        }
        if (self::is_mentor($userid)) {
            return true;
        }
        $qualifications = self::get_all_mentorship_qualifications($userid);
        return count($qualifications) > 0;
    }

    public static function get_all_mentorship_qualifications($userid = null) {
        global $CFG, $USER;
        if (is_null($userid)) {
            $userid = $USER->id;
        }
        require_once($CFG->dirroot . '/lib/badgeslib.php');
        $userbadges = \badges_get_user_badges($userid);
        if (empty($userbadges)) {
            return [];
        }
        $mentorbadgetypes = self::get_mentor_badge_types();
        $badgetopics = [];
        foreach ($userbadges as $userbadge) {
            if (empty($userbadge->courseid)) {
                continue;
            }
            if (!in_array(strtolower($userbadge->name), $mentorbadgetypes)) {
                continue;
            }
            $coursetopics = \local_thi_learning_companions\get_course_topics($userbadge->courseid);
            if (!empty($coursetopics)) {
                $badgetopics = array_merge($badgetopics, $coursetopics);
            }
        }
        $badgetopics = array_unique($badgetopics);
        return $badgetopics;
    }

    /**
     * returns an array of topics for which a given users has already accepted mentorship
     * @param $userid
     * @return int[]|string[]
     * @throws \dml_exception
     */
    public static function get_mentorship_topics($userid = null) {
        global $USER, $DB;
        if (is_null($userid)) {
            $userid = $USER->id;
        }
        $mentortopics = $DB->get_records('thi_lc_mentors', ['userid' => $userid], '', 'topic');
        $topics = array_keys($mentortopics);
        return $topics;
    }

    public static function get_mentorship_topics_of_mentors($mentors) {
        $topics = [];
        foreach ($mentors as $mentor) {
            $topics = array_merge($topics, self::get_mentorship_topics($mentor->userid));
        }
        $topics = array_unique($topics);
        return $topics;
    }

    /**
     * returns all the courses that belong a mentor's topics
     * @param $userid
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function get_courses_of_mentor($userid = null) {
        global $USER, $DB;
        if (is_null($userid)) {
            $userid = $USER->id;
        }
        $mentortopics = self::get_mentorship_topics($userid);
        if (empty($mentortopics)) {
            return [];
        }
        list($condition, $params) = $DB->get_in_or_equal($mentortopics);
        $courses = $DB->get_records_sql(
                "SELECT DISTINCT c.*
                        FROM {course} c
                        JOIN {context} ctx ON ctx.instanceid = c.id AND ctx.contextlevel = '" . CONTEXT_COURSE . "'
                        JOIN {customfield_data} d ON d.contextid = ctx.id
                        JOIN {customfield_field} f ON f.id = d.instanceid
                        WHERE d.value " . $condition,
            $params
        );
        return $courses;
    }

    /**
     * make the user mentor for a topic
     * @param $userid
     * @param $topic
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function assign_mentorship($userid, $topic) {
        global $DB;
        $availabletopics = self::get_new_mentorship_qualifications($userid);
        $topicclean = addslashes(strip_tags($topic)); // For XSS-safe output on the page.
        if (!in_array($topic, $availabletopics)) {
            $assignedtopics = self::get_mentorship_topics($userid);
            if (in_array($topic, $assignedtopics)) {
                $message = get_string('mentorship_already_assigned', 'local_thi_learning_companions', $topicclean);
                \core\notification::info($message);
                return;
            } else {
                throw new \moodle_exception(
                    'mentorship_error_invalid_topic_assignment',
                    'local_thi_learning_companions',
                    '',
                    $topicclean
                );
                return;
            }
        }
        try {
            $obj = new \stdClass();
            $obj->userid = $userid;
            $obj->topic = $topic;
            $mentorid = $DB->insert_record('thi_lc_mentors', $obj);
            self::assign_mentor_role($userid);

            mentor_assigned::make($userid, $mentorid)->trigger();
            \core\notification::success(
                get_string('mentorship_assigned_to_topic', 'local_thi_learning_companions',
                    $topicclean)
            );
        } catch (\Exception $e) {
            \core\notification::error(get_string('mentorship_error_unknown', 'local_thi_learning_companions', $e->getMessage()));
        }
    }

    /**
     * assigns the mentor role to a user (if the user doesn't have it yet)
     * @param $userid
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function assign_mentor_role($userid) {
        global $DB;
        $roleid = $DB->get_field('role', 'id', ['shortname' => 'lc_mentor']);
        if (!$roleid) {
            $roleid = self::create_mentor_role();
        }
        if (user_has_role_assignment($userid, $roleid)) {
            return;
        }
        $context = \context_system::instance();
        role_assign($roleid, $userid, $context);
    }

    /**
     * @param int $askedby
     * @param int $mentorid
     * @param string $topic
     * @param string $title
     * @param string $question
     * @return void
     * @throws \dml_exception
     */
    public static function add_mentor_question(int $askedby, int $mentorid, string $topic, string $title, string $question) {
        global $DB;
        $obj = new \stdClass();
        $obj->askedby = $askedby;
        $obj->mentorid = $mentorid;
        $obj->topic = $topic;
        $obj->title = $title;
        $obj->question = $question;
        $obj->timeclosed = 0;
        $obj->timecreated = time();
        $questionid = $DB->insert_record('thi_lc_mentor_questions', $obj);
        question_created::make($askedby, $questionid, $topic, $mentorid)->trigger();
    }

    /**
     * returns all mentors that have qualified for topics of courses that a given user is enrolled in
     * @param $userid
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function get_mentors_of_users_courses($userid = null) {
        global $USER, $DB;
        if (is_null($userid) && isloggedin()) {
            $userid = $USER->id;
        } else if (!isloggedin()) {
            return [];
        }
        $coursetopics = \local_thi_learning_companions\get_topics_of_user_courses($userid);
        if (empty($coursetopics)) {
            return [];
        }
        list($topicscondition, $topicsparams) = $DB->get_in_or_equal($coursetopics);
        $mentors = $DB->get_records_sql(
            "SELECT DISTINCT u.* FROM {user} u
                JOIN {thi_lc_mentors} m ON m.userid = u.id
                WHERE u.deleted = 0
                AND m.topic " . $topicscondition,
            $topicsparams
        );
        foreach ($mentors as $mentor) {
            $mentor->badges = badges_get_user_badges($mentor->id);
        }
        return $mentors;
    }

    /**
     * @param $userid
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function get_learning_nugget_comments($userid = null) {
        global $USER, $DB;
        $userid = is_null($userid) ? $USER->id : $userid;
        $courses = self::get_courses_of_mentor($userid);
        $courseids = array_keys($courses);
        if (empty($courseids)) {
            return [];
        }
        $learningnuggets = self::get_learning_nuggets_of_courses($courseids);
        if (empty($learningnuggets)) {
            return [];
        }
        $learningnuggetids = array_keys($learningnuggets);
        list($condition, $params) = $DB->get_in_or_equal($learningnuggetids);
        $config = get_config('local_thi_learning_companions');
        $limit = intval($config->latest_comments_max_amount);
        $latestcomments = $DB->get_records_sql(
            "SELECT DISTINCT comment.*,
                    u.id AS userid, u.firstname, u.lastname, u.email, u.username,
                    cm.id AS cmid, cm.module, cm.instance as cminstance,
                    c.id AS courseid, c.category AS coursecategory, c.fullname AS coursefullname, c.shortname AS courseshortname,
                    m.name AS modulename
                    FROM {comments} comment
                    JOIN {context} ctx ON comment.contextid = ctx.id AND ctx.contextlevel = " . CONTEXT_MODULE . "
                    JOIN {course_modules} cm ON cm.id = ctx.instanceid
                    JOIN {modules} m ON m.id = cm.module
                    JOIN {course} c ON c.id = cm.course
                    JOIN {user} u ON u.id = comment.userid
                    WHERE cm.id " . $condition . "
                    ORDER BY comment.timecreated DESC
                    LIMIT " . $limit,
            $params
        );
        foreach ($latestcomments as $latestcomment) {
            $latestcomment->nuggettitle = $DB->get_field($latestcomment->modulename, 'name', ['id' => $latestcomment->cminstance]);
        }

        return $latestcomments;
    }

    /**
     * @param $courseids
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     */
    protected static function get_learning_nuggets_of_courses($courseids) {
        global $DB;
        list($condition, $params) = $DB->get_in_or_equal($courseids);
        $nuggets = $DB->get_records_sql('select * from {course_modules} WHERE course ' . $condition, $params);
        return $nuggets;
    }

    protected static function get_latest_comment_of_nugget($cmid) {

    }

    /**
     * creates the mentor role if it doesn't exist yet
     * @return int
     * @throws \coding_exception
     */
    protected static function create_mentor_role() {
        $name = get_string('mentor_role', 'local_thi_learning_companions');
        $shortname = 'lc_mentor';
        $description = get_string('mentor_role_description', 'local_thi_learning_companions');
        $roleid = \create_role($name, $shortname, $description);
        // assign capabilities to the role.
        assign_capability('local/thi_learning_companions:mentor_ismentor', CAP_ALLOW, $roleid);
        return $roleid;
    }

    /**
     * @return array
     * @throws \dml_exception
     */
    public static function get_mentor_badge_types() {
        $mentorbadges = get_config('local_thi_learning_companions', 'badgetypes_for_mentors');
        $mentorbadges = strtolower($mentorbadges);
        $mentorbadges = explode(',', $mentorbadges);
        $mentorbadges = array_map('trim', $mentorbadges);
        return $mentorbadges;
    }

}
