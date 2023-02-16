<?php
defined('MOODLE_INTERNAL') || die();

global $CFG, $DB;

if ($oldversion < 2023021601) {
    require_once __DIR__ . '/../lib.php';
//    local_learningcompanions\db\create_course_customfields();
    upgrade_plugin_savepoint(true, 2023021601, 'local', 'learningcompanions');

}