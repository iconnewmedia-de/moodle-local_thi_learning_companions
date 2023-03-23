<?php
namespace local_learningcompanions;
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
 * Enrol users form.
 *
 * Simple form to search for users and add them using a manual enrolment to this course.
 *
 * @package enrol_manual
 * @copyright 2016 Damyon Wiese
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/formslib.php');

class select_users_to_invite_form extends \moodleform
{

    /**
     * Form definition.
     * @return void
     */
    public function definition()
    {
        global $PAGE, $DB, $CFG;
        $context = $this->_customdata->context;
        $groupid = $context->id;
        $mform = $this->_form;
        $mform->setDisableShortforms();
        $mform->disable_form_change_checker();
      // ICTODO: Check if the user has the permission to invite users to this group. (S)he must be part of this group, for example
        $mform->addElement('header', 'main', get_string('inviteusers', 'local_learningcompanions'));
        $options = array(
            'ajax' => 'local_learningcompanions_get_invitable_users',
            'multiple' => true,
            'groupid' => $groupid,
            'perpage' => $CFG->maxusersperpage
        );
        $mform->addElement('autocomplete', 'userlist', get_string('selectusers', 'local_learningcompanions'), array(), $options);
        $this->add_action_buttons();
    }

    /**
     * Validate the submitted form data.
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK (true allowed for backwards compatibility too).
     */
    public function validation($data, $files)
    {
        $errors = parent::validation($data, $files);
        if (!empty($data['startdate']) && !empty($data['timeend'])) {
            if ($data['startdate'] >= $data['timeend']) {
                $errors['timeend'] = get_string('enroltimeendinvalid', 'enrol');
            }
        }
        return $errors;
    }
}
