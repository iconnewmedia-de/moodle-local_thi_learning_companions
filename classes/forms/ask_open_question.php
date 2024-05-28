<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
namespace local_thi_learning_companions\forms;

use local_thi_learning_companions\groups;
defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . "/formslib.php");;
require_once(__DIR__ . "/../../locallib.php");;

class ask_open_question extends \moodleform {
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('html', get_string('ask_open_question_description', 'local_thi_learning_companions'));
        $this->add_topic();
        $this->add_subject();
        $this->add_question();
        $this->add_action_buttons(false, get_string('ask_question', 'local_thi_learning_companions'));
    }

    private function add_question() {
        $mform = $this->_form;
        $mform->addElement('editor', 'question', get_string('question', 'local_thi_learning_companions'));
        $mform->setType('question', PARAM_TEXT);
        $mform->addRule('question', get_string('required'), 'required', null, 'client');
    }

    private function add_subject() {
        $mform = $this->_form;
        $mform->addElement('text', 'subject', get_string('subject', 'local_thi_learning_companions'));
        $mform->setType('subject', PARAM_TEXT);
        $mform->addRule('subject', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('subject', 'subject', 'local_thi_learning_companions');
    }

    private function add_topic() {
        global $USER;
        $mform = $this->_form;
        $usertopics = \local_thi_learning_companions\get_topics_of_user_courses($USER->id);
        $choices = [];
        foreach($usertopics as $usertopic) {
            $usertopic = trim($usertopic);
            if (!empty($usertopic)) {
                $choices[$usertopic] = $usertopic;
            }
        }

        // ICTODO: to be discussed - should the topics be related to groups?
        // Mentors get their role based on their courses, not their groups.
        $topics = array_merge([0 => get_string('please_choose', 'local_thi_learning_companions')], $choices);

        $mform->addElement('select', 'topic', get_string('topic', 'local_thi_learning_companions'), $topics);
        $mform->setType('topic', PARAM_TEXT);
        $mform->addRule('topic', 'required', 'required');
        $mform->addHelpButton('topic', 'topic', 'local_thi_learning_companions');
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if ($data['topic'] == 0) {
            $errors['topic'] = get_string('please_choose_a_topic', 'local_thi_learning_companions');
        }
        return $errors;
    }
}
