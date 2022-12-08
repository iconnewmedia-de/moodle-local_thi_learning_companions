<?php

namespace local_learningcompanions;

use core\message\message;

class messages {
    private static function initNewMessage(string $messageName): message {
        $message = new \core\message\message();
        $message->component = 'local_learningcompanions';
        $message->notification = 1;

        if ($messageName) {
            $message->name = $messageName;
        }

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
        $message->subject = get_string('message_group_join_requested_subject', 'local_learningcompanions');
        $message->fullmessage = get_string('message_group_join_requested_body', 'local_learningcompanions', [
            'receivername' => fullname($recipient),
            'groupname' => $group->name,
            'sendername' => fullname($requestedUser),
        ]);
        $message->fullmessageformat = FORMAT_PLAIN;
        $message->fullmessagehtml = get_string('message_group_join_requested_body_html', 'local_learningcompanions', [
            'receivername' => fullname($recipient),
            'groupname' => $group->name,
            'sendername' => fullname($requestedUser)
        ]);
        $message->smallmessage = get_string('message_group_join_requested_small', 'local_learningcompanions', $group->name);
        //ICTODO: Change to correct URL
        $message->contexturl = (new \moodle_url('/local/learningcompanions/group/search.php', ['id' => $group->id]))->out(false);
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
        $message->subject = get_string('message_appointed_to_admin_subject', 'local_learningcompanions');
        $message->fullmessage = get_string('message_appointed_to_admin_body', 'local_learningcompanions', [
            'receivername' => fullname($newAdmin),
            'groupname' => $group->name,
            'sendername' => fullname($appointedBy),
        ]);
        $message->fullmessageformat = FORMAT_PLAIN;
        $message->fullmessagehtml = get_string('message_appointed_to_admin_body_html', 'local_learningcompanions', [
            'receivername' => fullname($newAdmin),
            'groupname' => $group->name,
            'sendername' => fullname($appointedBy)
        ]);
        $message->smallmessage = get_string('message_appointed_to_admin_small', 'local_learningcompanions', $group->name);
        $message->contexturl = (new \moodle_url('/local/learningcompanions/chat.php', ['groupid' => $group->id]))->out(false);
        $message->contexturlname = $group->name;


        message_send($message);
    }
}
