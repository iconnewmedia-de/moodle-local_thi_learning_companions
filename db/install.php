<?php
defined('MOODLE_INTERNAL') || die();
function xmldb_local_learningcompanions_install() {
    require_once __DIR__ . '/lib.php';
    local_learningcompanions\db\create_course_customfields();
}