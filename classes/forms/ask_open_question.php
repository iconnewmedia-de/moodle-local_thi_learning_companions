<?php

namespace local_learningcompanions\forms;

use local_learningcompanions\groups;

global $CFG;
require_once $CFG->libdir . "/formslib.php";

class ask_open_question extends \moodleform {
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('static', 'description', get_string('ask_open_question_description', 'local_learningcompanions'));
        $this->addTopic();
        $this->addSubject();
        $this->addQuestion();
        $this->add_action_buttons(false, get_string('ask_question', 'local_learningcompanions'));
    }

    private function addQuestion() {
        $mform = $this->_form;
        $mform->addElement('editor', 'question', get_string('question', 'local_learningcompanions'));
        $mform->setType('question', PARAM_TEXT);
        $mform->addRule('question', get_string('required'), 'required', null, 'client');
    }

    private function addSubject() {
        $mform = $this->_form;
        $mform->addElement('text', 'subject', get_string('subject', 'local_learningcompanions'));
        $mform->setType('subject', PARAM_TEXT);
        $mform->addRule('subject', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('subject', 'subject', 'local_learningcompanions');
    }

    private function addTopic() {
        $mform = $this->_form;
        $topics = [0 => get_string('please_choose', 'local_learningcompanions')] + groups::get_all_keywords();

        $mform->addElement('select', 'topic', get_string('topic', 'local_learningcompanions'), $topics);
        $mform->addHelpButton('topic', 'topic', 'local_learningcompanions');
    }
}
