<?php

namespace local_learningcompanions\event;

class group_searched extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['context'] = \context_system::instance();
    }

    public static function get_name() {
        return get_string('event_group_searched', 'local_learningcompanions');
    }

    public function get_description() {
        return "The user with id '$this->userid' used the group search.";
    }

    public static function make(int $userId) {
        return self::create([
            'userid' => $userId,
        ]);
    }
}