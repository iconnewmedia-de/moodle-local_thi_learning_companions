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
namespace local_thi_learning_companions;

/**
 * Collection of event observers
 */
class eventobservers {
    /**
     * Observer for \core\event\course_module_created event.
     * automatically adds a new comments block to every newly created course module
     * @param \core\event\course_module_created $event
     * @return void
     */
    public static function course_module_created(\core\event\course_module_created $event) {
        $data = $event->get_data();
        $modulename = $data['other']['modulename'];
        require_once(__DIR__ . "/../locallib.php");
        $whitelist = get_moduletypes_for_commentblock();
        if (!in_array($modulename, $whitelist)) {
            return;
        }
        $parentcontextid = $data['contextid'];
        require_once(__DIR__ . '/../locallib.php');
        create_comment_block($parentcontextid, $modulename);
    }

    /**
     * Handles the event when a course gets restored. We might have to add the comment block.
     * @param \core\event\course_restored $event
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function course_restored(\core\event\course_restored $event) {
        global $CFG;
        require_once($CFG->dirroot . '/local/thi_learning_companions/locallib.php');
        \local_thi_learning_companions\add_comment_blocks();
    }

    /**
     * When users receive a new badge: Notify them that they can become a mentor (for certain badges)
     * @param \core\event\badge_awarded $event
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function badge_awarded(\core\event\badge_awarded $event) {
        global $DB;
        $config = get_config('local_thi_learning_companions');
        $badgetypesformentors = $config->badgetypes_for_mentors;
        $badgetypesformentors = explode(',', $badgetypesformentors);
        $data = $event->get_data();
        if (empty($data['courseid'])) {
            return;
        }
        $badge = $DB->get_record('badge', ['id' => $data['objectid']]);
        $sendnotification = false;
        $badgename = strtolower($badge->name);
        foreach ($badgetypesformentors as $badgetype) {
            $badgetype = strtolower(trim($badgetype));
            if (strpos($badgename, $badgetype) > -1) {
                $sendnotification = true;
                break;
            }
        }
        if ($sendnotification) {
            \local_thi_learning_companions\messages::send_mentor_qualification_message($data['courseid'], $data['relateduserid']);
        }
    }

    /**
     * checks if the settings for local_thi_learning_companions | commentactivities have changed
     * if yes, calls the function that ensures that all listed activity types have a comment block
     * @param \core\event\config_log_created $event
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function config_log_created(\core\event\config_log_created $event) {
        $data = $event->get_data();
        $info = $data['other'];
        if ($info['plugin'] !== 'local_thi_learning_companions' || $info['name'] !== 'commentactivities') {
            return;
        }
        if ($info['oldvalue'] == $info['value']) {
            return;
        }
        require_once(__DIR__ . "/../locallib.php");
        \local_thi_learning_companions\add_comment_blocks();
    }
}
