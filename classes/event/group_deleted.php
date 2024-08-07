<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace local_thi_learning_companions\event;

/**
 * The group_deleted event class.
 *
 * Das Projekt THISuccessAI (FBM202-EA-1690-07540) wird im Rahmen der Förderlinie „Hochschulen durch Digitalisierung stärken“
 * durch die Stiftung Innovation in der Hochschulehre gefördert.
 *
 * @package     local_thi_learning_companions
 * @category    event
 * @copyright   2023 ICON Vernetzte Kommunikation GmbH <spiros.tzanetatos@iconnewmedia.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class group_deleted extends \core\event\base {

    // For more information about the Events API please visit {@link https://docs.moodle.org/dev/Events_API}.
    /**
     * initializes the event
     * @return void
     * @throws \dml_exception
     */
    protected function init() {
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'local_thi_learning_companions_groups';
        $this->context = \context_system::instance();
    }

    /**
     * validates the data
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
    }

    /**
     * returns the event's name
     * @return \lang_string|string
     * @throws \coding_exception
     */
    public static function get_name() {
        return get_string('event_group_deleted', 'local_thi_learning_companions');
    }

    /**
     * returns the event's description
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' deleted a group with id '$this->objectid'.";
    }

    /**
     * Creates the event
     * @param int $deleterid
     * @param int $groupid
     * @return \core\event\base
     * @throws \coding_exception
     */
    public static function make(int $deleterid, int $groupid) {
        return self::create([
            'objectid' => $groupid,
            'userid' => $deleterid,
        ]);
    }
}
