<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
namespace local_thi_learning_companions\event;

class question_responded extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->context = \context_system::instance();
        $this->data['objecttable'] = 'thi_lc_chat_comment';
    }

    public static function get_name() {
        return get_string('event_question_responded', 'local_thi_learning_companions');
    }

    public function get_description() {
        return "The user with id '$this->userid' has responded to the question chat ".
            "with id '{$this->other['questionid']}' with the answer with the id '$this->objectid'.";
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

    public static function make(int $userid, int $chatid, int $chatcommentid) {
        return self::create([
            'objectid' => $chatcommentid,
            'userid' => $userid,
            'other' => [
                'questionid' => $chatid,
            ],
        ]);
    }
}
