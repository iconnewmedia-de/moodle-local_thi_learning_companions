<?php

namespace local_learningcompanions;

class mentors {

    /**
     * @param $topic
     * @param $supermentorsonly
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function get_mentors($topic = null, $supermentorsonly = false): array {
        global $CFG, $DB, $OUTPUT;
        // ICTODO: There is definitely a better way to query this.
        // ICTODO: Maybe split into basic and extended function.

        require_once($CFG->dirroot.'/local/learningcompanions/lib.php');

        $sql = 'SELECT DISTINCT m.userid,
                       u.*
                  FROM {lc_mentors} m
             LEFT JOIN {user} u ON u.id = m.userid';
        $params = array();

        if (!is_null($topic)) {
            $sql .= ' WHERE m.topic = ?';
            $params[] = (int)$topic;
        }

        $mentors = $DB->get_records_sql($sql, $params);

        $sql = 'SELECT m.topic,
                       k.keyword
                  FROM {lc_mentors} m
             LEFT JOIN {lc_keywords} k ON k.id = m.topic
                 WHERE m.userid = ?';

        foreach ($mentors as $mentor) {
            $mentor->issupermentor = self::is_supermentor($mentor->userid);

            if ($supermentorsonly && !$mentor->issupermentor) {
                unset($mentors[$mentor->userid]);
            } else {
                $mentor->topics = $DB->get_records_sql($sql, [$mentor->userid]);
                $mentor->fullname = fullname($mentor);
                $mentor->profileurl = $CFG->wwwroot.'/user/profile.php?id='.$mentor->userid;
                $mentor->userpic = $OUTPUT->user_picture($mentor, [
                    'link' => false, 'visibletoscreenreaders' => false,
                    'class' => 'userpicture'
                ]);
                $mentor->status = get_user_status($mentor->userid);

                $topiclist = [];
                foreach ($mentor->topics as $mentorTopic) {
                    $topiclist[] = $mentorTopic->keyword;
                }
                $mentor->topiclist = implode(', ', $topiclist);
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
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function is_supermentor($userid = null) {
        global $USER;
        $context = \context_system::instance();
        $userid = is_null($userid) ? $USER->id : $userid;
        return has_capability('local/learningcompanions:mentor_issupermentor', $context, $userid); // Maybe access restriction by database entry
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
     * @param bool $extended
     * @return array
     * @throws \dml_exception
     */
    public static function get_my_asked_questions(int $userid, bool $extended = false): array {
        global $DB;

        if ($extended) {
            $sql = 'SELECT q.*,
                           FROM_UNIXTIME(q.timecreated, "%d.%m.%Y") AS dateasked,
                           FROM_UNIXTIME(q.timeclosed, "%d.%m.%Y - %H:%i") AS dateclosed,
                           (SELECT COUNT(a.id) FROM {lc_mentor_answers} a WHERE a.questionid = q.id) answercount,
                           (SELECT FROM_UNIXTIME(MAX(a.timecreated), "%d.%m.%Y") FROM {lc_mentor_answers} a WHERE a.questionid = q.id) lastactivity
                      FROM {lc_mentor_questions} q
                     WHERE q.askedby = ?';
            return $DB->get_records_sql($sql, array($userid));
        }

        return $DB->get_records('lc_mentor_questions', array('askedby' => $userid));
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
                           k.keyword,
                           FROM_UNIXTIME(q.timecreated, "%d.%m.%Y") AS dateasked,
                           FROM_UNIXTIME(q.timeclosed, "%d.%m.%Y - %H:%i") AS dateclosed,
                           (SELECT COUNT(a.id) FROM {lc_mentor_answers} a WHERE a.questionid = q.id) answercount,
                           (SELECT FROM_UNIXTIME(MAX(a.timecreated), "%d.%m.%Y") FROM {lc_mentor_answers} a WHERE a.questionid = q.id) lastactivity
                      FROM {lc_mentor_questions} q
                 LEFT JOIN {lc_keywords} k ON k.id = q.topic';
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

            if (!is_null($topics)) {
                $topiclist = implode(',', $topics);
                $sql .= ($conditions < 1) ? ' WHERE q.topic IN ('.$topiclist.')' : ' AND q.topic IN ('.$topiclist.')';
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

    public static function get_latest_nugget_comments($userid, $cmid = null) {
        return array();
    }

    /**
     * @param $questionid
     * @return bool
     * @throws \dml_exception
     */
    public static function delete_asked_question($questionid): bool {
        global $DB;
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
    public static function get_mentor_qualifications() {
        global $USER, $DB, $CFG;
        require_once $CFG->dirroot . '/lib/badgeslib.php';
        $userBadges = \badges_get_user_badges($USER->id);
        $mentorCourses = $DB->get_records('lc_mentors', array('userid' => $USER->id), '', 'courseid');
        $mentorCourseIDs = array_keys($mentorCourses);
        $qualifiedCourses = [];
        foreach($userBadges as $userBadge) {
            if (!is_null($userBadge->courseid) && !in_array($userBadge->courseid, $mentorCourseIDs)) {
                $qualifiedCourses[$userBadge->courseid] = $DB->get_record('course', array('id' => $userBadge->courseid));
            }
        }
        return $qualifiedCourses;
    }
}
