<?php

namespace local_thi_learning_companions\event;

class comment_created extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'thi_lc_chat_comment';
        $this->context = \context_system::instance();
    }

    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->data['objectid'])) {
            throw new \coding_exception('The \'objectid\' value must be set.');
        }

        if (!isset($this->data['userid'])) {
            throw new \coding_exception('The \'userid\' value must be set.');
        }

        if (!isset($this->data['other']['chatid'])) {
            throw new \coding_exception('The \'other[chatid]\' value must be set.');
        }
    }

    public static function get_name() {
        return get_string('event_comment_created', 'local_thi_learning_companions');
    }

    public function get_description() {
        return "The user with id '$this->userid' created a comment with id '$this->objectid' in chat with id '{$this->other['chatid']}'.";
    }

    public static function make(int $userId, int $chatId, int $commentId) {
        return self::create([
            'objectid' => $commentId,
            'userid' => $userId,
            'other' => [
                'chatid' => $chatId
            ]
        ]);
    }
}