<?php

namespace local_learningcompanions;

class messages {
    public static function send_group_join_requested_notification(int $requestedUserId, int $recipientId, int $groupId) {
        global $DB;

        $requestedUser = $DB->get_record('user', ['id' => $requestedUserId]);
        $recipient = $DB->get_record('user', ['id' => $recipientId]);
        $group = new group($groupId);

        $message = new \core\message\message();
        $message->component = 'local_learningcompanions';
        $message->name = 'group_join_requested';
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
        $message->notification = 1;
        //ICTODO: Change to correct URL
        $message->contexturl = (new \moodle_url('/local/learningcompanions/group/search.php', ['id' => $group->id]))->out(false);
        $message->contexturlname = $group->name;

        message_send($message);
    }
}
