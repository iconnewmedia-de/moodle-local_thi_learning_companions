<?php
defined('MOODLE_INTERNAL') || die();

global $CFG, $DB;

if ($oldversion < 2023090401) {
    require_once __DIR__ . "/../../locallib.php";
    local_learningcompanions\add_comment_blocks();
    upgrade_plugin_savepoint(true, 2023090401, 'local', 'learningcompanions');
}
