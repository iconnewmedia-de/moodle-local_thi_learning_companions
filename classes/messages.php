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

use core\message\message;

/**
 * Class for sending messages related to this plugin, such as notifications about join requests.
 */
class messages {
    /**
     * @param string $messagename
     * @return message
     */
    private static function init_new_message(string $messagename): message {
        $message = new \core\message\message();
        $message->component = 'local_thi_learning_companions';
        $message->notification = 1;
        $message->fullmessageformat = FORMAT_PLAIN;
        $message->name = $messagename;

        return $message;
    }

    /**
     * @param int $requesteduserid
     * @param int $recipientid
     * @param int $groupid
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function send_group_join_requested_notification(int $requesteduserid, int $recipientid, int $groupid) {
        global $DB;

        $requesteduser = $DB->get_record('user', ['id' => $requesteduserid]);
        $recipient = $DB->get_record('user', ['id' => $recipientid]);
        $group = new group($groupid);

        $message = self::init_new_message('group_join_requested');
        $message->userfrom = $requesteduser;
        $message->userto = $recipient;
        $message->subject = get_string('message_group_join_requested_subject', 'local_thi_learning_companions');
        $message->fullmessage = get_string('message_group_join_requested_body', 'local_thi_learning_companions', [
            'receivername' => fullname($recipient),
            'groupname' => $group->name,
            'sendername' => fullname($requesteduser),
        ]);
        $message->fullmessagehtml = get_string('message_group_join_requested_body_html', 'local_thi_learning_companions', [
            'receivername' => fullname($recipient),
            'groupname' => $group->name,
            'sendername' => fullname($requesteduser),
        ]);
        $message->smallmessage = get_string('message_group_join_requested_small', 'local_thi_learning_companions', $group->name);
        $message->contexturl = (new \moodle_url('/local/thi_learning_companions/group/confirm_requested_join.php'))->out(false);
        $message->contexturlname = $group->name;

        message_send($message);
    }

    /**
     * @param int $newadminid
     * @param int $groupid
     * @param int|null $appointedbyuserid
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function send_appointed_to_admin_notification(int $newadminid, int $groupid, int $appointedbyuserid = null) {
        global $DB;

        $newadmin = $DB->get_record('user', ['id' => $newadminid]);
        $appointedby = $DB->get_record('user', ['id' => $appointedbyuserid]);
        $group = new group($groupid);

        $message = self::init_new_message('appointed_to_admin');
        $message->userfrom = $appointedby;
        $message->userto = $newadmin;
        $message->subject = get_string('message_appointed_to_admin_subject', 'local_thi_learning_companions');
        $message->fullmessage = get_string('message_appointed_to_admin_body', 'local_thi_learning_companions', [
            'receivername' => fullname($newadmin),
            'groupname' => $group->name,
            'sendername' => fullname($appointedby),
        ]);
        $message->fullmessagehtml = get_string('message_appointed_to_admin_body_html', 'local_thi_learning_companions', [
            'receivername' => fullname($newadmin),
            'groupname' => $group->name,
            'sendername' => fullname($appointedby),
        ]);
        $message->smallmessage = get_string('message_appointed_to_admin_small', 'local_thi_learning_companions', $group->name);
        $message->contexturl = (new \moodle_url('/local/thi_learning_companions/chat.php', ['groupid' => $group->id]))->out(false);
        $message->contexturlname = $group->name;

        message_send($message);
    }

    /**
     * @param $userid
     * @param $groupid
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function send_group_join_accepted_notification($userid, $groupid) {
        global $DB, $USER;

        $user = $DB->get_record('user', ['id' => $userid]);
        $group = new group($groupid);

        $message = self::init_new_message('group_join_accepted');
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
            'sendername' => fullname($USER),
        ]);
        $message->smallmessage = get_string('message_group_join_accepted_small', 'local_thi_learning_companions', $group->name);
        $message->contexturl = (new \moodle_url('/local/thi_learning_companions/chat.php', ['groupid' => $group->id]))->out(false);
        $message->contexturlname = $group->name;

        message_send($message);
    }

    /**
     * @param $userid
     * @param $groupid
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function send_group_join_denied_notification($userid, $groupid) {
        global $DB, $USER;

        $user = $DB->get_record('user', ['id' => $userid]);
        $group = new group($groupid);

        $message = self::init_new_message('group_join_denied');
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
            'sendername' => fullname($USER),
        ]);
        $message->smallmessage = get_string('message_group_join_denied_small', 'local_thi_learning_companions', $group->name);
        $message->contexturl = (new \moodle_url('/local/thi_learning_companions/chat.php', ['groupid' => $group->id]))->out(false);
        $message->contexturlname = $group->name;

        message_send($message);
    }

    /**
     * @param $userid
     * @param $groupid
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function send_invited_to_group($userid, $groupid) {
        global $DB, $USER;

        $user = $DB->get_record('user', ['id' => $userid]);
        $group = new group($groupid);

        $message = self::init_new_message('invited_to_group');
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
            'sendername' => fullname($USER),
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
        $user = $DB->get_record('user', ['id' => $userid]);
        $message = new \core\message\message();
        $message->component = 'local_thi_learning_companions'; // Your plugin's name.
        $message->name = 'notification_qualified_mentor'; // Your notification name from message.php.
        $message->userfrom = \core_user::get_noreply_user(); // If the message is 'from' a specific user you can set them here.
        $message->userto = $user;
        // ICTODO: get course topic instead and display that in the email. We will have to use a custom course profile field.
        $course = $DB->get_record('course', ['id' => $courseid]);

        $link = $CFG->wwwroot . '/local/thi_learning_companions/mentor/manage.php';
        $placeholders = [
          'username' => $user->username,
          'firstname' => $user->firstname,
          'lastname' => $user->lastname,
          'email' => $user->email,
          'userfullname' => trim(join(' ', [$user->firstname, $user->lastname])),
          'courseid' => $courseid,
          'coursefullname' => $course->fullname,
          'courseshortname' => $course->shortname,
          'link' => $link,
        ];
        $message->subject = get_string('message_qualified_mentor_subject', 'local_thi_learning_companions', $placeholders);
        $message->fullmessagehtml = get_string('message_qualified_mentor_body', 'local_thi_learning_companions', $placeholders);
        $message->fullmessage = strip_tags($message->fullmessagehtml);
        $message->fullmessageformat = FORMAT_PLAIN;
        $message->fullmessagehtml = nl2br($message->fullmessage);
        $message->smallmessage = get_string(
            'message_qualified_mentor_smallmessage',
            'local_thi_learning_companions',
            $placeholders
        );
        $message->notification = 1; // Because this is a notification generated from Moodle, not a user-to-user message.
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
        $whoasked = $DB->get_record('user', ['id' => $question->askedby, 'deleted' => 0]);
        if (!$whoasked) {
            return;
        }
        $readabledate = userdate($question->timecreated);
        $message->component = 'local_thi_learning_companions'; // Your plugin's name.
        $message->name = 'notification_unanswered_question'; // Your notification name from message.php.
        $message->userfrom = \core_user::get_noreply_user(); // If the message is 'from' a specific user you can set them here.
        $message->userto = $tutor;
        $placeholders = [
            'user' => $tutor,
            'user_firstname' => $tutor->firstname,
            'user_lastname' => $tutor->lastname,
            'askedby' => $whoasked,
            'askedby_firstname' => $whoasked->firstname,
            'askedby_lastname' => $whoasked->lastname,
            'dateasked' => $readabledate,
            'topic' => $question->topic,
            'question' => $question->question,
            'title' => $question->title,
            'sitename' => $SITE->fullname,
        ];
        $message->subject = get_string('message_unanswered_question_subject', 'local_thi_learning_companions',
            $placeholders);
        $message->fullmessagehtml = get_string('message_unanswered_question_body', 'local_thi_learning_companions',
            $placeholders);
        $message->fullmessage = strip_tags($message->fullmessagehtml);
        $message->fullmessageformat = FORMAT_PLAIN;
        $message->fullmessagehtml = nl2br($message->fullmessage);
        $message->smallmessage = get_string('message_unanswered_question_smallmessage', 'local_thi_learning_companions',
            $placeholders);
        $message->notification = 1; // Because this is a notification generated from Moodle, not a user-to-user message.
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
        $mincomments = intval($config->supermentor_minimum_ratings);
        $message = new \core\message\message();
        $message->component = 'local_thi_learning_companions'; // Your plugin's name.
        $message->name = 'appointed_to_supermentor';
        $message->userfrom = \core_user::get_noreply_user(); // If the message is 'from' a specific user you can set them here.
        $message->userto = $userid;
        $message->subject = get_string('youve_become_supermentor_subject', 'local_thi_learning_companions');
        $message->fullmessagehtml = $message->fullmessage = get_string(
            'youve_become_supermentor_body',
            'local_thi_learning_companions',
            $mincomments
        );
        $message->fullmessageformat = FORMAT_HTML;
        $message->smallmessage = get_string('youve_become_supermentor_short', 'local_thi_learning_companions');
        $message->notification = 1; // Because this is a notification generated from Moodle, not a user-to-user message.
        message_send($message);
    }
}
