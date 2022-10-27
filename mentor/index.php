<?php
require_once '../../../config.php';
require_once '../lib.php';

$context = context_system::instance();
require_capability( 'local/learningcompanions:mentor_view', $context);

$PAGE->set_context($context);
$PAGE->set_url($CFG->wwwroot.'/local/learningcompanions/mentor/index.php');
$PAGE->set_pagelayout('standard');

$PAGE->requires->css('/local/learningcompanions/vendor/DataTables/datatables.min.css');
$PAGE->requires->css('/local/learningcompanions/vendor/balloon.css');

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_learningcompanions/mentor_index', array(
    'cfg' => $CFG
));
echo $OUTPUT->footer();
