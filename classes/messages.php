<?php

namespace local_thi_learning_companions;

use core\message\message;

class messages {
    private static function initNewMessage(string $messageName): message {
        $message = new \core\message\message();
        $message->component = 'local_thi_learning_companions';
        $message->notification = 1;
        $message->fullmessageformat = FORMAT_PLAIN;
        $message->name = $messageName;

        return $message;
    }

    public static function send_group_join_requested_notification(int $requestedUserId, int $recipientId, int $groupId) {
        global $DB;

        $requestedUser = $DB->get_record('user', ['id' => $requestedUserId]);
        $recipient = $DB->get_record('user', ['id' => $recipientId]);
        $group = new group($groupId);

        $message = self::initNewMessage('group_join_requested');
        $message->userfrom = $requestedUser;
        $message->userto = $recipient;
        $message->subject = get_string('message_group_join_requested_subject', 'local_thi_learning_companions');
        $message->fullmessage = get_string('message_group_join_requested_body', 'local_thi_learning_companions', [
            'receivername' => fullname($recipient),
            'groupname' => $group->name,
            'sendername' => fullname($requestedUser),
        ]);
        $message->fullmessagehtml = get_string('message_group_join_requested_body_html', 'local_thi_learning_companions', [
            'receivername' => fullname($recipient),
            'groupname' => $group->name,
            'sendername' => fullname($requestedUser)
        ]);
        $message->smallmessage = get_string('message_group_join_requested_small', 'local_thi_learning_companions', $group->name);
        $message->contexturl = (new \moodle_url('/local/thi_learning_companions/group/confirm_requested_join.php'))->out(false);
        $message->contexturlname = $group->name;

        message_send($message);
    }

    public static function send_appointed_to_admin_notification(int $newAdminId, int $groupId, int $appointedByUserId = null) {
        global $DB;

        $newAdmin = $DB->get_record('user', ['id' => $newAdminId]);
        $appointedBy = $DB->get_record('user', ['id' => $appointedByUserId]);
        $group = new group($groupId);

        $message = self::initNewMessage('appointed_to_admin');
        $message->userfrom = $appointedBy;
        $message->userto = $newAdmin;
        $message->subject = get_string('message_appointed_to_admin_subject', 'local_thi_learning_companions');
        $message->fullmessage = get_string('message_appointed_to_admin_body', 'local_thi_learning_companions', [
            'receivername' => fullname($newAdmin),
            'groupname' => $group->name,
            'sendername' => fullname($appointedBy),
        ]);
        $message->fullmessagehtml = get_string('message_appointed_to_admin_body_html', 'local_thi_learning_companions', [
            'receivername' => fullname($newAdmin),
            'groupname' => $group->name,
            'sendername' => fullname($appointedBy)
        ]);
        $message->smallmessage = get_string('message_appointed_to_admin_small', 'local_thi_learning_companions', $group->name);
        $message->contexturl = (new \moodle_url('/local/thi_learning_companions/chat.php', ['groupid' => $group->id]))->out(false);
        $message->contexturlname = $group->name;

        message_send($message);
    }

    public static function send_group_join_accepted_notification($userId, $groupId) {
        global $DB, $USER;

        $user = $DB->get_record('user', ['id' => $userId]);
        $group = new group($groupId);

        $message = self::initNewMessage('group_join_accepted');
        $message->userfrom = $USER;
        $message->userto = $user;
        $message->subject = get_string('message_group_join_accepted_subject', 'local_thi_learning_companions');
        $message->fullmessage = get_string('message_group_join_accepted_body', 'local_thi_learning_companions', [
            'receivername' => fullname($user),
            'groupname' => $group->name,
            'sendername' => fullname($USER),
        ]);
        $message->fullmessagehtml = get_string('message_group_join_accepted_body_html', 'local_thi_learning_companions', [
            'receivername' => fullname($user),
            'groupname' => $group->name,
            'sendername' => fullname($USER)
        ]);
        $message->smallmessage = get_string('message_group_join_accepted_small', 'local_thi_learning_companions', $group->name);
        $message->contexturl = (new \moodle_url('/local/thi_learning_companions/chat.php', ['groupid' => $group->id]))->out(false);
        $message->contexturlname = $group->name;

        message_send($message);
    }

    public static function send_group_join_denied_notification($userId, $groupId) {
        global $DB, $USER;

        $user = $DB->get_record('user', ['id' => $userId]);
        $group = new group($groupId);

        $message = self::initNewMessage('group_join_denied');
        $message->userfrom = $USER;
        $message->userto = $user;
        $message->subject = get_string('message_group_join_denied_subject', 'local_thi_learning_companions');
        $message->fullmessage = get_string('message_group_join_denied_body', 'local_thi_learning_companions', [
            'receivername' => fullname($user),
            'groupname' => $group->name,
            'sendername' => fullname($USER),
        ]);
        $message->fullmessagehtml = get_string('message_group_join_denied_body_html', 'local_thi_learning_companions', [
            'receivername' => fullname($user),
            'groupname' => $group->name,
            'sendername' => fullname($USER)
        ]);
        $message->smallmessage = get_string('message_group_join_denied_small', 'local_thi_learning_companions', $group->name);
        $message->contexturl = (new \moodle_url('/local/thi_learning_companions/chat.php', ['groupid' => $group->id]))->out(false);
        $message->contexturlname = $group->name;

        message_send($message);
    }

    public static function send_invited_to_group($userid, $groupid) {
        global $DB, $USER;

        $user = $DB->get_record('user', ['id' => $userid]);
        $group = new group($groupid);

        $message = self::initNewMessage('invited_to_group');
        $message->userfrom = $USER;
        $message->userto = $user;
        $message->subject = get_string('message_invited_to_group_subject', 'local_thi_learning_companions');
        $message->fullmessage = get_string('message_invited_to_group_body', 'local_thi_learning_companions', [
            'receivername' => fullname($user),
            'groupname' => $group->name,
            'sendername' => fullname($USER),
        ]);
        $message->fullmessagehtml = get_string('message_invited_to_group_body_html', 'local_thi_learning_companions', [
            'receivername' => fullname($user),
            'groupname' => $group->name,
            'sendername' => fullname($USER)
        ]);
        $message->smallmessage = get_string('message_invited_to_group_small', 'local_thi_learning_companions', $group->name);
        $message->contexturl = (new \moodle_url('/local/thi_learning_companions/chat.php', ['groupid' => $group->id]))->out(false);
        $message->contexturlname = $group->name;

        message_send($message);
    }

    /**
     * informs a user that (s)he has qualified to become a mentor
     * @param $courseid
     * @return false|int|mixed
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function send_mentor_qualification_message($courseid, $userid) {
        global $DB, $CFG;
        $user = $DB->get_record('user', array('id' => $userid));
        $message = new \core\message\message();
        $message->component = 'local_thi_learning_companions'; // Your plugin's name
        $message->name = 'notification_qualified_mentor'; // Your notification name from message.php
        $message->userfrom = \core_user::get_noreply_user(); // If the message is 'from' a specific user you can set them here
        $message->userto = $user;
        // ICTODO: get course topic instead and display that in the email. We will have to use a custom course profile field
        $course = $DB->get_record('course', array('id' => $courseid));

        $link = $CFG->wwwroot . '/local/thi_learning_companions/mentor/manage.php';
        $placeholders = array(
          'username' => $user->username,
          'firstname' => $user->firstname,
          'lastname' => $user->lastname,
          'email' => $user->email,
          'userfullname' => trim(join(' ', array($user->firstname, $user->lastname))),
          'courseid' => $courseid,
          'coursefullname' => $course->fullname,
          'courseshortname' => $course->shortname,
          'link' => $link
        );
        $message->subject = get_string('message_qualified_mentor_subject', 'local_thi_learning_companions', $placeholders);
        $message->fullmessagehtml = get_string('message_qualified_mentor_body', 'local_thi_learning_companions', $placeholders);
        $message->fullmessage = strip_tags($message->fullmessagehtml);
        $message->fullmessageformat = FORMAT_PLAIN;
        $message->fullmessagehtml = nl2br($message->fullmessage);
        $message->smallmessage = get_string('message_qualified_mentor_smallmessage', 'local_thi_learning_companions', $placeholders);
        $message->notification = 1; // Because this is a notification generated from Moodle, not a user-to-user message
        $message->contexturl = (new \moodle_url('/course/'))->out(false);
        $message->contexturlname = 'Course list';
        $messageid = message_send($message);
        return $messageid;
    }

    /**
     * @param $tutor
     * @param $question
     * @return false|int|mixed|void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function send_tutor_unanswered_question_message($tutor, $question) {
        global $DB, $SITE;
        $message = new \core\message\message();
        $whoAsked = $DB->get_record('user', array('id' => $question->askedby, 'deleted' => 0));
        if (!$whoAsked) {
            return;
        }
        $readableDate = userdate($question->timecreated);
        $message->component = 'local_thi_learning_companions'; // Your plugin's name
        $message->name = 'notification_unanswered_question'; // Your notification name from message.php
        $message->userfrom = \core_user::get_noreply_user(); // If the message is 'from' a specific user you can set them here
        $message->userto = $tutor;
        $placeholders = array(
            'user' => $tutor,
            'user_firstname' => $tutor->firstname,
            'user_lastname' => $tutor->lastname,
            'question' => $question,
            'askedby' => $whoAsked,
            'askedby_firstname' => $whoAsked->firstname,
            'askedby_lastname' => $whoAsked->lastname,
            'dateasked' => $readableDate,
            'topic' => $question->topic,
            'question' => $question->question,
            'title' => $question->title,
            'sitename' => $SITE->fullname
        );
        $message->subject = get_string('message_unanswered_question_subject', 'local_thi_learning_companions',
            $placeholders);
        $message->fullmessagehtml = get_string('message_unanswered_question_body', 'local_thi_learning_companions',
            $placeholders);
        $message->fullmessage = strip_tags($message->fullmessagehtml);
        $message->fullmessageformat = FORMAT_PLAIN;
        $message->fullmessagehtml = nl2br($message->fullmessage);
        $message->smallmessage = get_string('message_unanswered_question_smallmessage', 'local_thi_learning_companions',
            $placeholders);
        $message->notification = 1; // Because this is a notification generated from Moodle, not a user-to-user message
        $message->contexturl = (new \moodle_url('/course/'))->out(false);
        $message->contexturlname = 'Course list';
        $messageid = message_send($message);
        return $messageid;
    }

    /**
     * notifies a user that (s)he has just become a supermentor by reaching the minimum amount of positive comment ratings
     * @param $userid
     * @return void
     * @throws \coding_exception
     */
    public static function notify_supermentor($userid) {
        $config = get_config('local_thi_learning_companions');
        $minComments = intval($config->supermentor_minimum_ratings);
        $message = new \core\message\message();
        $message->component = 'local_thi_learning_companions'; // Your plugin's name
        $message->name = 'appointed_to_supermentor';
        $message->userfrom = \core_user::get_noreply_user(); // If the message is 'from' a specific user you can set them here
        $message->userto = $userid;
        $message->subject = get_string('youve_become_supermentor_subject', 'local_thi_learning_companions');
        $message->fullmessagehtml = $message->fullmessage = get_string('youve_become_supermentor_body', 'local_thi_learning_companions', $minComments);
        $message->fullmessageformat = FORMAT_HTML;
        $message->smallmessage = get_string('youve_become_supermentor_short', 'local_thi_learning_companions');
        $message->notification = 1; // Because this is a notification generated from Moodle, not a user-to-user message
        message_send($message);
    }
}
