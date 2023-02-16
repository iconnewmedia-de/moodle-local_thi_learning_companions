<?php
// ICTODO: create a page for managing a user's mentorships:
// Which courses have I qualified for?
// Which courses have I agreed to become a mentor for?


require_once '../../../config.php';
require_once '../lib.php';

$context = context_system::instance();
require_login();

$PAGE->set_context($context);
$PAGE->set_url($CFG->wwwroot . '/local/learningcompanions/mentor/manage.php');
$PAGE->set_pagelayout('standard');
$PAGE->requires->js_call_amd('local_learningcompanions/mentor', 'init');
$PAGE->requires->css('/local/learningcompanions/vendor/DataTables/datatables.min.css');
$PAGE->requires->css('/local/learningcompanions/vendor/balloon.css');
$PAGE->navbar->add(get_string('navbar_mentors', 'local_learningcompanions'));
$PAGE->navbar->add(get_string('navbar_mentorquestions', 'local_learningcompanions'), new moodle_url('/local/learningcompanions/mentor/index.php'));
