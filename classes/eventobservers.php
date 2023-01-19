<?php
namespace local_learningcompanions;

class eventobservers {
    /**
     * Observer for \core\event\course_module_created event.
     *
     * @param \core\event\course_module_created $event
     * @return void
     */
    public static function course_module_created(\core\event\course_module_created $event) {
        global $DB;
        $data = $event->get_data();
        $modulename = $data['other']['modulename'];
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

      // ICTODO: add comments block with activity context for this new course module
    }
}