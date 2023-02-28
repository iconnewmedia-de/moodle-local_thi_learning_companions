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
 * A scheduled task for local_learningcompanions cron.
 *
 * @package    local_learningcompanions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_learningcompanions\task;

use local_learningcompanions\groups;

defined('MOODLE_INTERNAL') || die();


/**
 * @package    local_learningcompanions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cron_task extends \core\task\scheduled_task {

    // Use the logging trait to get some nice, juicy, logging.
    use \core\task\logging_trait;


    /**
     * @return string
     */
    public function get_name() {
        return get_string('crontask', 'local_learningcompanions');
    }

    /**
     * Execute the scheduled task.
     */
    public function execute() {
        global $CFG, $DB;
        $this->send_unanswered_questions_to_tutor();
    }

    protected static function send_unanswered_questions_to_tutor() {
        global $DB;
        $unansweredQuestions = self::get_unanswered_questions();
        if (empty($unansweredQuestions)) {
            return;
        }
        $config = get_config('local_learningcompanions');
        $tutorRoleName = trim($config->tutorrole_shortname);
        $tutorRoleID = $DB->get_field('role', 'id', array('shortname' => $tutorRoleName));
        if (!$tutorRoleID) {
            return;
        }


        foreach($unansweredQuestions as $question) {
            if (empty($question->topic)) { // shouldn't happen, but you never know
                continue;
            }
            $tutors = $DB->get_records_sql(
                "SELECT DISTINCT u.*
                  FROM {role_assignments} r
                  JOIN {user} u ON u.id = r.userid
                  JOIN {user_enrolments} ue ON ue.userid = u.id
                  JOIN {enrol} e ON e.id = ue.enrolid
                  JOIN {customfield_data} cd ON cd.courseid = e.courseid
                  JOIN {customfield_field} cf ON cf.id = cd.fieldid
                  WHERE r.roleid = ?
                    AND u.deleted = 0
                    AND cd.value = ?
                    AND cf.shortname = 'topic'",
                array($tutorRoleID, $question->topic)
            );
            foreach($tutors as $tutor) {
                \local_learningcompanions\messages::send_tutor_unanswered_question_message($tutor, $question);
            }
        }
    }

    protected function get_unanswered_questions() {
        global $DB;
        $config = get_config('local_learningcompanions');
        $timelimit = $config->inform_tutors_about_unanswered_questions_after_x_days;
        $xDaysAgo = time() - $timelimit * DAYSECS;
        $unanswered = $DB->get_records_sql("SELECT q.*
            FROM {lc_mentor_questions} q
            LEFT JOIN {lc_chat} c ON c.relatedid = q.id AND c.chattype = '" . groups::CHATTYPE_MENTOR . "'
            LEFT JOIN {lc_chat_comments} cmnt ON cmnt.chatid = c.id
            LEFT JOIN {lc_tutor_notifications} n ON n.questionid = q.id
            WHERE q.timecreated < ?
            AND cmnt.id IS NULL
            AND n.id IS NULL",
        array($xDaysAgo));
        return $unanswered;
    }
}
