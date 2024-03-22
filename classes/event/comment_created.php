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

/**
 *
 */
class comment_created extends \core\event\base {
    /**
     * @return void
     * @throws \dml_exception
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'thi_lc_chat_comment';
        $this->context = \context_system::instance();
    }

    /**
     * @return void
     * @throws \coding_exception
     */
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

    /**
     * @return \lang_string|string
     * @throws \coding_exception
     */
    public static function get_name() {
        return get_string('event_comment_created', 'local_thi_learning_companions');
    }

    /**
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' " .
            "created a comment with id '$this->objectid' " .
            "in chat with id '{$this->other['chatid']}'.";
    }

    /**
     * @param int $userid
     * @param int $chatid
     * @param int $commentid
     * @return \core\event\base
     * @throws \coding_exception
     */
    public static function make(int $userid, int $chatid, int $commentid) {
        return self::create([
            'objectid' => $commentid,
            'userid' => $userid,
            'other' => [
                'chatid' => $chatid,
            ],
        ]);
    }
}
