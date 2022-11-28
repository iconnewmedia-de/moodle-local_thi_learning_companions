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
 * Learning Companions Cleanup Task
 *
 * @package   local_learningcompanions
 * @copyright 2022 ICON Vernetzte Kommunikation GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_learningcompanions\task;


/**
 * Learning Companions Cleanup Task
 *
 * @package   local_learningcompanions
 * @copyright 2022 ICON Vernetzte Kommunikation GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cleanup extends \core\task\scheduled_task
{

    public function get_name()
    {
        return get_string('cleanup_task', 'local_learningcompanions');
    }

    public function execute()
    {
        // ICTODO: remove users from chats and groups who've been inactive for too long
        // ICTODO: remove or archive or purge chats and groups that have been unused for too long
    }
}