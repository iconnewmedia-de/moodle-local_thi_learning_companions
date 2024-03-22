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

class comment_reported extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'thi_lc_chat_comment';
        $this->context = \context_system::instance();
    }

    public static function get_name() {
        return get_string('event_comment_reported', 'local_thi_learning_companions');
    }

    public function get_description() {
        return "The user with id '$this->userid' reported the comment with id '$this->objectid'.";
    }

    public static function make(int $messageid, int $authorid) {
        return self::create([
            'objectid' => $messageid,
            'userid' => $authorid,
        ]);
    }
}
