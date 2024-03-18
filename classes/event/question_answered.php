<?php

namespace local_thi_learning_companions\event;

class question_answered extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->context = \context_system::instance();
        $this->data['objecttable'] = 'lc_mentor_questions';
    }

    public static function get_name() {
        return get_string('event_question_answered', 'local_thi_learning_companions');
    }

    public function get_description() {
        return "The user with id '$this->userid' marked the question '{$this->other['questionid']} as answered'.";
    }

    protected function validate_data() {
        if (!isset($this->data['objectid'])) {
            throw new \coding_exception('The \'objectid\' (answerId) must be set.');
        }

        if (!isset($this->data['userid'])) {
            throw new \coding_exception('The \'userid\' must be set.');
        }
    }

    public static function make(int $userId, int $questionId) {
        return self::create([
            'objectid' => $questionId,
            'userid' => $userId,
        ]);
    }
}