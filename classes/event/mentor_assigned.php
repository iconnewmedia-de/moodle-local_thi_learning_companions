<?php

namespace local_learningcompanions\event;

class mentor_assigned extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['context'] = \context_system::instance();
        $this->data['objecttable'] = 'lc_mentors';
    }

    public static function get_name() {
        return get_string('event_mentor_assigned', 'local_learningcompanions');
    }

    public function get_description() {
        return "The user with id '$this->userid' has become the mentor with id '$this->objectid'.";
    }

    protected function validate_data() {
        if (!isset($this->data['objectid'])) {
            throw new \coding_exception('The \'objectid\' (mentorId) must be set.');
        }

        if (!isset($this->data['userid'])) {
            throw new \coding_exception('The \'userid\' must be set.');
        }
    }

    public static function make(int $userId, int $mentorId) {
        return self::create([
            'objectid' => $mentorId,
            'userid' => $userId,
        ]);
    }
}