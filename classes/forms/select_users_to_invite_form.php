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

namespace local_thi_learning_companions;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/formslib.php');
/**
 * Enrol users form.
 *
 * Simple form to search for users and add them using a manual enrolment to this course.
 *
 * @package enrol_manual
 * @copyright 2016 Damyon Wiese
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class select_users_to_invite_form extends \moodleform {

    /**
     * Form definition.
     * @return void
     */
    public function definition() {
        $context = $this->_customdata->context;
        $groupid = $context->id;
        if (!groups::may_view_group($groupid)) {
            throw new \moodle_exception('no_permission_invite_group', 'local_thi_learning_companions');
        }
        $mform = $this->_form;
        $mform->setDisableShortforms();
        $mform->disable_form_change_checker();
        $mform->addElement('hidden', 'action', 'invite');
        $mform->setType('action', PARAM_TEXT);
        $mform->addElement('header', 'main', get_string('inviteusers', 'local_thi_learning_companions'));
        $options = [
            'ajax' => 'local_thi_learning_companions/invitation_potential_user_selector',
            'multiple' => true,
            'groupid' => $groupid,
            'perpage' => 100,
        ];

        $mform->addElement('autocomplete', 'userlist', get_string('selectusers', 'local_thi_learning_companions'), [], $options);
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
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (!empty($data['startdate']) && !empty($data['timeend'])) {
            if ($data['startdate'] >= $data['timeend']) {
                $errors['timeend'] = get_string('enroltimeendinvalid', 'enrol');
            }
        }
        return $errors;
    }
}
