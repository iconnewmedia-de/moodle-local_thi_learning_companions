<?php
namespace local_thi_learning_companions;

class eventobservers {
    /**
     * Observer for \core\event\course_module_created event.
     * automatically adds a new comments block to every newly created course module
     * @param \core\event\course_module_created $event
     * @return void
     */
    public static function course_module_created(\core\event\course_module_created $event) {
        global $DB;
        $data = $event->get_data();
        $modulename = $data['other']['modulename'];
        require_once __DIR__ . "/../locallib.php";
        $whitelist = get_moduletypes_for_commentblock();
        if (!in_array($modulename, $whitelist)) {
            return;
        }
        $parentcontextid = $data['contextid'];
        require_once __DIR__ . '/../locallib.php';
        create_comment_block($parentcontextid, $modulename);
    }

    /**
     * @param \core\event\course_restored $event
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function course_restored(\core\event\course_restored $event) {
        global $CFG;
        require_once $CFG->dirroot . '/local/thi_learning_companions/locallib.php';
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
        $badgeTypesForMentors = $config->badgetypes_for_mentors;
        $badgeTypesForMentors = explode(',', $badgeTypesForMentors);
        $data = $event->get_data();
        if (empty($data['courseid'])) {
            return;
        }
        $badge = $DB->get_record('badge', array('id' => $data['objectid']));
        $sendNotification = false;
        $badgename = strtolower($badge->name);
        foreach($badgeTypesForMentors as $badgeType) {
            $badgeType = strtolower(trim($badgeType));
            if (strpos($badgename, $badgeType) > -1) {
                $sendNotification = true;
                break;
            }
        }
        if ($sendNotification) {
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
        require_once __DIR__ . "/../locallib.php";
        \local_thi_learning_companions\add_comment_blocks();
    }
}