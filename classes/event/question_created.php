<?php

namespace local_thi_learning_companions\event;

class question_created extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->context = \context_system::instance();
        $this->data['objecttable'] = 'thi_lc_mentor_questions';
    }

    public static function get_name() {
        return get_string('event_question_created', 'local_thi_learning_companions');
    }

    public function get_description() {
        if ($this->relateduserid != 0) {
            return "The user with id '$this->userid' has created the question with id '$this->objectid' for mentor with id '$this->relateduserid'.";
        }
        return "The user with id '$this->userid' has created the question with id '$this->objectid'.";
    }

    protected function validate_data() {
        if (!isset($this->data['objectid'])) {
            throw new \coding_exception('The \'objectid\' (questionId) must be set.');
        }

        if (!isset($this->data['userid'])) {
            throw new \coding_exception('The \'userid\' must be set.');
        }

        if (!isset($this->data['other']['topic'])) {
            throw new \coding_exception('The \'other[topic]\' must be set.');
        }
    }

    public static function make(int $userId, int $questionId, string $topic, int $mentorId = 0) {
        return self::create([
            'objectid' => $questionId,
            'userid' => $userId,
            'relateduserid' => $mentorId,
            'other' => [
                'topic' => $topic,
            ]
        ]);
    }
}