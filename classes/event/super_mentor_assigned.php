<?php

namespace local_learningcompanions\event;

class super_mentor_assigned extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->context = \context_system::instance();
    }

    public static function get_name() {
        return get_string('event_super_mentor_assigned', 'local_learningcompanions');
    }

    public function get_description() {
        return "The user with id '$this->userid' has become a super mentor.";
    }

    protected function validate_data() {
        if (!isset($this->data['userid'])) {
            throw new \coding_exception('The \'userid\' must be set.');
        }
    }

    public static function make(int $userId) {
        return self::create([
            'userid' => $userId,
        ]);
    }
}