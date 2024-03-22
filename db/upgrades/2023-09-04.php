<?php
defined('MOODLE_INTERNAL') || die();

global $CFG, $DB;

if ($oldversion < 2023090401) {
    require_once(__DIR__ . "/../../locallib.php");;
    local_thi_learning_companions\add_comment_blocks();
    upgrade_plugin_savepoint(true, 2023090401, 'local', 'thi_learning_companions');
}
