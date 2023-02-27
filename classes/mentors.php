<?php
namespace local_learningcompanions;
require_once __DIR__ . "/../locallib.php";
require_once $CFG->libdir . "/badgeslib.php";
require_once $CFG->dirroot . "/badges/classes/badge.php";
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

        require_once($CFG->dirroot.'/local/learningcompanions/lib.php');

        $sql = 'SELECT DISTINCT m.userid, GROUP_CONCAT(m.topic) as topics,
                       u.*
                  FROM {lc_mentors} m
             LEFT JOIN {user} u ON u.id = m.userid
             ';
        $params = array();
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
//                $mentor->topics = $DB->get_records_sql($sql, [$mentor->userid]);
                $mentor->fullname = fullname($mentor);
                $mentor->profileurl = $CFG->wwwroot.'/user/profile.php?id='.$mentor->userid;
                $mentor->userpic = $OUTPUT->user_picture($mentor, [
                    'link' => false, 'visibletoscreenreaders' => false,
                    'class' => 'userpicture'
                ]);
                list($mentor->status, $mentor->statustext) = get_user_status($mentor->userid);
                $mentor->badges = badges_get_user_badges($mentor->id, 0, 0, 0, '', true);
                $mentor->badges = array_values($mentor->badges);
                foreach($mentor->badges as $badge) {
                    $badgeObj = new \badge($badge->id);
                    if (!is_null($badge->courseid)) {
                        $badgeContext = \context_course::instance($badge->courseid);
                    } else {
                        $badgeContext = $context;
                    }
                    $badge->image = print_badge_image($badgeObj, $badgeContext);
                }
                $topiclist = [];
                $topics = explode(',', $mentor->topics);
                foreach ($topics as $mentorTopic) {
                    $topiclist[] = array('topic' => trim($mentorTopic));
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
        return has_capability('local/learningcompanions:mentor_ismentor', $context, $userid); // Maybe access restriction by database entry
    }

    /**
     * @param $userid
     * @param $topic
     * @return bool
     * @throws \dml_exception
     */
    public static function is_mentor_for_topic($userid, $topic) {
        global $DB;
        return $DB->record_exists('lc_mentor', array('userid' => $userid, 'topic' => $topic));
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
        $config = get_config('local_learningcompanions');
        $minimumRatings = intval($config->supermentor_minimum_ratings);
        $countMentorRatings = self::count_mentor_ratings($userid);
        return $countMentorRatings >= $minimumRatings;
    }

    public static function count_mentor_ratings($userid = null) {
        global $USER, $DB;
        $userid = is_null($userid) ? $USER->id : $userid;
        $countMentorRatings = $DB->count_records_sql(
            "SELECT count(distinct cr.id)
                    FROM {lc_chat_comment} c
                     JOIN {lc_chat_comment_ratings} cr ON cr.commentid = c.id
                     WHERE c.userid = ?",
            array($userid)
        );
        return $countMentorRatings;
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
        return has_capability('local/learningcompanions:mentor_istutor', $context, $userid); // Maybe access restriction by database entry or teacher role
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
     * @param int[] $topicIds
     *
     * @return array
     */
    public static function get_mentor_questions_by_topics(array $topicIds): array {
        return question::get_all_questions_by_topics($topicIds);
    }

    /**
     * @param int|null   $userid
     * @param array|null $topics
     * @param bool       $onlyopen
     * @param bool       $extended
     * @return array
     * @throws \dml_exception
     */
    public static function get_all_mentor_questions(int $userid = null, array $topics = null, bool $onlyopen = false, bool $extended = false): array {
        global $DB;

        if ($extended) {
            $sql = 'SELECT q.*,
                           FROM_UNIXTIME(q.timecreated, "%d.%m.%Y") AS dateasked,
                           IF(q.timeclosed=0, "-", FROM_UNIXTIME(q.timeclosed, "%d.%m.%Y - %H:%i")) AS dateclosed,
                           (SELECT COUNT(a.id) FROM {lc_mentor_answers} a WHERE a.questionid = q.id) answercount,
                           (SELECT FROM_UNIXTIME(MAX(a.timecreated), "%d.%m.%Y") FROM {lc_mentor_answers} a WHERE a.questionid = q.id) lastactivity
                      FROM {lc_mentor_questions} q';
            $params = array();
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
                list($conditionTopics, $paramsTopics) = $DB->get_in_or_equal($topics);
                $sql .= ($conditions < 1) ? ' WHERE q.topic ' . $conditionTopics : ' AND q.topic ' . $conditionTopics;
                $params = $params + $paramsTopics;
                $conditions++;
            }

            return $DB->get_records_sql($sql, $params);
        }

        $params = array();
        if (!is_null($userid)) $params['mentorid'] = $userid;
        $questions = $DB->get_records('lc_mentor_questions', $params);
        if ($onlyopen) {
            $questions = array_filter($questions, function($question) {
                return is_null($question->timeclosed);
            });
        }

        return $questions;
    }

    public static function get_all_mentor_question_answers($questionid) {
        global $DB;

    }

    /**
     * @param $questionid
     * @return bool
     * @throws \dml_exception
     */
    public static function delete_asked_question($questionid): bool {
        global $DB;
        // ICTODO: delete records from lc_chat_comment instead, we don't use lc_mentor_answers anymore
        return $DB->delete_records('lc_mentor_answers', array('questionid' => $questionid))
            && $DB->delete_records('lc_mentor_questions', array('id' => $questionid));
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
        //         but in worst case we get an empty array.

        $sql = 'SELECT m.topic,
                       k.keyword
                  FROM {lc_mentors} m
             LEFT JOIN {lc_keywords} k ON k.id = m.topic
                 WHERE m.userid = ?';

        $keywords = $DB->get_records_sql($sql, array($userid));

        if ($idsonly) {
            $keywordlist = array();
            foreach ($keywords as $keyword) {
                $keywordlist[] = $keyword->topic;
            }
            return $keywordlist;
        }

        return $keywords;
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
        $mentorTopics = self::get_mentorship_topics($userid);
        $badgeTopics = self::get_all_mentorship_qualifications($userid);
        $newQualifications = array_diff($badgeTopics, $mentorTopics);
        return $newQualifications;
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
        require_once $CFG->dirroot . '/lib/badgeslib.php';
        $userBadges = \badges_get_user_badges($userid);
        if (empty($userBadges)) {
            return [];
        }
        $badgeTopics = [];
        foreach($userBadges as $userBadge) {
            if (empty($userBadge->courseid)) {
                continue;
            }
            $courseTopics = \local_learningcompanions\get_course_topics($userBadge->courseid);
            $badgeTopics = array_merge($badgeTopics, $courseTopics);
        }
        $badgeTopics = array_unique($badgeTopics);
        return $badgeTopics;
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
        $mentorTopics = $DB->get_records('lc_mentors', array('userid' => $userid), '', 'topic');
        $topics = array_keys($mentorTopics);
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
        $mentorTopics = self::get_mentorship_topics($userid);
        if (empty($mentorTopics)) {
            return [];
        }
        list($condition, $params) = $DB->get_in_or_equal($mentorTopics);
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
        $availableTopics = self::get_new_mentorship_qualifications($userid);
        $topicClean = addslashes(strip_tags($topic)); // for XSS-safe output on the page
        if (!in_array($topic, $availableTopics)) {
            $assignedTopics = self::get_mentorship_topics($userid);
            if (in_array($topic, $assignedTopics)) {
                $message = get_string('mentorship_already_assigned', 'local_learningcompanions', $topicClean);
                \core\notification::info($message);
                return;
            } else {
                print_error('mentorship_error_invalid_topic_assignment', 'local_learningcompaions', $topicClean);
                return;
            }
        }
        try {
            $obj = new \stdClass();
            $obj->userid = $userid;
            $obj->topic = $topic;
            $DB->insert_record('lc_mentors', $obj);
            self::assign_mentor_role($userid);
            \core\notification::success(get_string('mentorship_assigned_to_topic', 'local_learningcompanions', $topicClean));
        } catch(\Exception $e) {
            \core\notification::error(get_string('mentorship_error_unknown', 'local_learningcompanions', $e->getMessage()));
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
        $roleid = $DB->get_field('role', 'id', array('shortname' => 'lc_mentor'));
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
        $DB->insert_record('lc_mentor_questions', $obj);
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
        } elseif(!isloggedin()) {
            return [];
        }
        $courseTopics = \local_learningcompanions\get_topics_of_user_courses($userid);
        if (empty($courseTopics)) {
            return [];
        }
        list($topicsCondition, $topicsParams) = $DB->get_in_or_equal($courseTopics);
        $mentors = $DB->get_records_sql(
            "SELECT DISTINCT u.* FROM {user} u
                JOIN {lc_mentors} m ON m.userid = u.id
                WHERE u.deleted = 0
                AND m.topic " . $topicsCondition,
            $topicsParams
        );
        foreach($mentors as $mentor) {
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
        $userid = is_null($userid)?$USER->id:$userid;
        $courses = self::get_courses_of_mentor($userid);
        $courseIDs = array_keys($courses);
        if (empty($courseIDs)) {
            return [];
        }
        $learningNuggets = self::get_learning_nuggets_of_courses($courseIDs);
        if (empty($learningNuggets)) {
            return [];
        }
        $learningNuggetIDs = array_keys($learningNuggets);
        list($condition, $params) = $DB->get_in_or_equal($learningNuggetIDs);
        $config = get_config('local_learningcompanions');
        $limit = intval($config->latest_comments_max_amount);
        $latestComments = $DB->get_records_sql(
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
        foreach($latestComments as $latestComment) {
            $latestComment->nuggettitle = $DB->get_field($latestComment->modulename, 'name', array('id' => $latestComment->cminstance));
        }

        return $latestComments;
    }

    /**
     * @param $courseIDs
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     */
    protected static function get_learning_nuggets_of_courses($courseIDs) {
        global $DB;
        list($condition, $params) = $DB->get_in_or_equal($courseIDs);
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
        $name = get_string('mentor_role', 'local_learningcompanions');
        $shortname = 'lc_mentor';
        $description = get_string('mentor_role_description', 'local_learningcompanions');
        $roleid = \create_role($name, $shortname, $description);
        // ICTODO: assign capabilities to the role
        return $roleid;
    }

}
