<?php

namespace local_learningcompanions\event;

class question_responded extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['context'] = \context_system::instance();
        $this->data['objecttable'] = 'lc_chat_comment';
    }

    public static function get_name() {
        return get_string('event_question_responded', 'local_learningcompanions');
    }

    public function get_description() {
        return "The user with id '$this->userid' has responded to the question chat with id '{$this->other['questionid']}' with the answer with the id '$this->objectid'.";
    }

    protected function validate_data() {
        if (!isset($this->data['objectid'])) {
            throw new \coding_exception('The \'objectid\' (questionId) must be set.');
        }

        if (!isset($this->data['userid'])) {
            throw new \coding_exception('The \'userid\' must be set.');
        }

        if (!isset($this->other['questionid'])) {
            throw new \coding_exception('The \'other[questionid]\' must be set.');
        }
    }

    public static function make(int $userId, int $chatId, int $chatCommentId) {
        return self::create([
            'objectid' => $chatCommentId,
            'userid' => $userId,
            'other' => [
                'questionid' => $chatId,
            ],
        ]);
    }
}