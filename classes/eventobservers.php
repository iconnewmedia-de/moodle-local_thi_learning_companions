<?php
namespace local_learningcompanions;

use core\message\message;

class eventobservers {
    /**
     * Observer for \core\event\course_module_created event.
     * automatically adds a new comments block to every newly created course module
     * @param \core\event\course_module_created $event
     * @return void
     */
    public static function course_module_created(\core\event\course_module_created $event) {
        global $DB;
        $data = $event->get_data();
        $modulename = $data['other']['modulename'];
        $config = get_config('local_learningcompanions');
        $whitelist = explode(',', $config->commentactivities);
        array_walk($whitelist, 'trim');
        if (!in_array($modulename, $whitelist)) {
            return;
        }
        $parentcontextid = $data['contextid'];
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
     * When users receive a new badge: Notify them that they can become a mentor (for certain badges)
     * @param \core\event\badge_awarded $event
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function badge_awarded(\core\event\badge_awarded $event) {
        global $DB;
        $config = get_config('local_learningcompanions');
        $badgeTypesForMentors = $config->badgetypes_for_mentors;
        $badgeTypesForMentors = explode(',', $badgeTypesForMentors);
        array_walk($badgeTypesForMentors, 'trim');
        array_walk($badgeTypesForMentors, 'strtolower');
        $data = $event->get_data();
        if (empty($data['courseid'])) {
            return;
        }
        $badge = $DB->get_record('badge', array('id' => $data['objectid']));
        $sendNotification = false;
        $badgename = strtolower($badge->name);
        foreach($badgeTypesForMentors as $badgeTypesForMentor) {
            if (strpos($badgeTypesForMentor, $badgename) > -1) {
                $sendNotification = true;
                break;
            }
        }
        if ($sendNotification) {
            self::send_mentor_qualification_message($data['courseid'], $data['relateduserid']);
        }
    }

    /**
     * @param $courseid
     * @return false|int|mixed
     * @throws \coding_exception
     * @throws \dml_exception
     */
    protected static function send_mentor_qualification_message($courseid, $userid) {
        global $DB, $CFG;
        $user = $DB->get_record('user', array('id' => $userid));
        $message = new \core\message\message();
        $message->component = 'local_learningcompanions'; // Your plugin's name
        $message->name = 'notification_qualified_mentor'; // Your notification name from message.php
        $message->userfrom = \core_user::get_noreply_user(); // If the message is 'from' a specific user you can set them here
        $message->userto = $user;
        $course = $DB->get_record('course', array('id' => $courseid));
        $link = $CFG->wwwroot . '/local/learningcompanions/mentor/manage.php';
        $message->subject = get_string('message_qualified_mentor_subject', 'local_learningcompanions', array('user' => $user, 'course' => $course, 'link' => $link));
        $message->fullmessagehtml = get_string('message_qualified_mentor_body', 'local_learningcompanions', array('user' => $user, 'course' => $course));
        $message->fullmessage = strip_tags($message->fullmessagehtml);
        $message->fullmessageformat = FORMAT_PLAIN;
        $message->fullmessagehtml = $message->fullmessage;
        $message->smallmessage = get_string('message_qualified_mentor_smallmessage', 'local_learningcompanions', array('user' => $user, 'course' => $course));
        $message->notification = 1; // Because this is a notification generated from Moodle, not a user-to-user message
        $message->contexturl = (new \moodle_url('/course/'))->out(false);
        $message->contexturlname = 'Course list';
        $messageid = message_send($message);
        return $messageid;
    }
}