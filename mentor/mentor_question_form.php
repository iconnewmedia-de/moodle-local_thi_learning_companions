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

/**
 * Das Projekt THISuccessAI (FBM202-EA-1690-07540) wird im Rahmen der Förderlinie „Hochschulen durch Digitalisierung stärken“
 * durch die Stiftung Innovation in der Hochschulehre gefördert.
 *
 * @package     local_thi_learning_companions
 * @copyright   2022 ICON Vernetzte Kommunikation GmbH <info@iconnewmedia.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_thi_learning_companions\mentor;
use local_thi_learning_companions\mentors;
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/lib/formslib.php');
require_once(__DIR__ . "/../locallib.php");

/**
 * form for asking a question to a mentor
 */
class mentor_question_form extends \moodleform {
    /**
     * the form definition
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function definition() {
        global $DB;
        $mform = $this->_form;
        $customdata = $this->_customdata;
        $mentorid = $customdata['mentor'];
        if ($mentorid === 0) {
            // ICTODO: get all mentors for the topics of my courses and create a selector, including the option "all mentors".
            $mentoroptions = [-1 => get_string('all_mentors', 'local_thi_learning_companions')];
            $mentors = mentors::get_mentors_of_users_courses();
            foreach ($mentors as $mentor) {
                $mentoroptions[$mentor->id] = $mentor->firstname . " " . $mentor->lastname;
            }
            $mform->addElement('select', 'mentor', get_string('mentor', 'local_thi_learning_companions'), $mentoroptions);
        } else {
            $mentor = $DB->get_record('user', ['id' => $mentorid, 'deleted' => 0], '*', MUST_EXIST);
            $mentorname = \fullname($mentor);
            $mform->addElement('hidden', 'mentor', $mentorid);
            $mform->addElement('static', 'mentorname', get_string('mentor', 'local_thi_learning_companions'), $mentorname);
        }
        $usertopics = \local_thi_learning_companions\get_topics_of_user_courses();
        $topicoptions = array_combine($usertopics, $usertopics);
        $mform->addElement(
            'select',
            'questiontopic',
            get_string('mentor_question_topic', 'local_thi_learning_companions'),
            $topicoptions
        );
        $mform->setType('questiontopic', PARAM_TEXT);
        $mform->addElement('text', 'subject', get_string('mentor_question_subject', 'local_thi_learning_companions'));
        $mform->setType('subject', PARAM_TEXT);
        $mform->addRule('subject', 'required', 'required');
        $mform->addElement('editor', 'question', get_string('mentor_question_body', 'local_thi_learning_companions'));
        $mform->setType('question', PARAM_TEXT);
        $mform->addRule('question', 'required', 'required');
        $this->add_action_buttons(false, get_string('submit_question', 'local_thi_learning_companions'));
    }
}
