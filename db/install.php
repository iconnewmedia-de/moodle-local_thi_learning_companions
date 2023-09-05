<?php
defined('MOODLE_INTERNAL') || die();
function xmldb_local_learningcompanions_install() {
    require_once __DIR__ . '/lib.php';
    require_once __DIR__ . '/../locallib.php';
    local_learningcompanions\db\create_course_customfields();
    local_learningcompanions\db\create_status_profile_field();
    local_learningcompanions\add_comment_blocks();
}