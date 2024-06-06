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

/**
 * Das Projekt THISuccessAI (FBM202-EA-1690-07540) wird im Rahmen der Förderlinie „Hochschulen durch Digitalisierung stärken“
 * durch die Stiftung Innovation in der Hochschulehre gefördert.
 *
 * @package     local_thi_learning_companions
 * @copyright   2022 ICON Vernetzte Kommunikation GmbH <info@iconnewmedia.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_thi_learning_companions\task;

/**
 * Learning Companions Cleanup Task
 */
class cleanup extends \core\task\scheduled_task {
    /**
     * returns the task name
     * @return \lang_string|string
     * @throws \coding_exception
     */
    public function get_name() {
        return get_string('cleanup_task', 'local_thi_learning_companions');
    }

    /**
     * execute the task
     * @return void
     */
    public function execute() {
        // ICTODO: remove users from chats and groups who've been inactive for too long.
        // ICTODO: remove or archive or purge chats and groups that have been unused for too long.
    }
}
