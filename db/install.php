<?php
defined('MOODLE_INTERNAL') || die();
function xmldb_local_thi_learning_companions_install() {
    require_once __DIR__ . '/lib.php';
    require_once __DIR__ . '/../locallib.php';
    local_thi_learning_companions\db\create_course_customfields();
    local_thi_learning_companions\db\create_status_profile_field();
    local_thi_learning_companions\add_comment_blocks();
}