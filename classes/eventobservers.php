<?php
namespace local_learningcompanions;

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
        $config = get_config('local_learningcompanions');
        $whitelist = explode(',', $config->commentactivities);
        array_walk($whitelist, 'trim');
        if (!in_array($modulename, $whitelist)) {
            return;
        }
        $parentcontextid = $data['contextid'];
        $block = new \stdClass();

        $block->blockname = 'comments';
        $block->parentcontextid = $parentcontextid;
        $block->showinsubcontexts = '';
        $block->pagetypepattern = 'mod-' . $modulename . '-*';
        $block->subpagepattern = '';
        $block->defaultregion = 'side-pre';
        $block->defaultweight = '2';
        $block->configdata = '';
        $block->timecreated = time();
        $block->timemodified = time();

        $DB->insert_record('block_instances', $block);
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
        $config = get_config('local_learningcompanions');
        $badgeTypesForMentors = $config->badgetypes_for_mentors;
        $badgeTypesForMentors = explode(',', $badgeTypesForMentors);
        array_walk($badgeTypesForMentors, 'trim');
        array_walk($badgeTypesForMentors, 'strtolower');
        $data = $event->get_data();
        if (empty($data['courseid'])) {
            return;
        }
        $badge = $DB->get_record('badge', array('id' => $data['objectid']));
        $sendNotification = false;
        $badgename = strtolower($badge->name);
        foreach($badgeTypesForMentors as $badgeTypesForMentor) {
            if (strpos($badgeTypesForMentor, $badgename) > -1) {
                $sendNotification = true;
                break;
            }
        }
        if ($sendNotification) {
            \local_learningcompanions\messages::send_mentor_qualification_message($data['courseid'], $data['relateduserid']);
        }
    }


}