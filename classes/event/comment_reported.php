<?php

namespace local_learningcompanions\event;

class comment_reported extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'lc_chat_comment';
        $this->context = \context_system::instance();
    }

    public static function get_name() {
        return get_string('event_comment_reported', 'local_learningcompanions');
    }

    public function get_description() {
        return "The user with id '$this->userid' reported the comment with id '$this->objectid'.";
    }

    public static function make(int $messageId, int $authorId) {
        return self::create([
            'objectid' => $messageId,
            'userid' => $authorId,
        ]);
    }
}