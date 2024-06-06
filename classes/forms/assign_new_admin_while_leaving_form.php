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

namespace local_thi_learning_companions\forms;
defined('MOODLE_INTERNAL') || die();
global $CFG;

use context;
use core_form\dynamic_form;
use local_thi_learning_companions\group;
use local_thi_learning_companions\groups;
use moodle_url;

require_once($CFG->libdir . "/formslib.php");

/**
 * The form for assigning a new admin when the current admin leaves a group
 */
class assign_new_admin_while_leaving_form extends dynamic_form {
    /**
     * @var group
     */
    private $group;

    /**
     * the form definition
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    protected function definition() {
        $mform = $this->_form;
        $groupid = $this->_ajaxformdata['groupId'];
        $group = \local_thi_learning_companions\groups::get_group_by_id($groupid);

        $possibleadmins = $this->get_possible_admins($group);

        $mform->addElement(
            'static',
            'description',
            get_string('assign_new_admin_while_leaving_description', 'local_thi_learning_companions')
        );
        $mform->addElement('hidden', 'groupId', $this->_ajaxformdata['groupId']);
        $mform->addElement('select', 'newAdmin', get_string('choose_new_admin', 'local_thi_learning_companions'), $possibleadmins);
        $mform->setDefault('newAdmin', $group->chat->get_last_active_userid(true));
        $this->add_action_buttons(false, get_string('leave_group', 'local_thi_learning_companions'));
    }

    /**
     * Returns the list of users that can be selected as new admin
     * @param group $group
     * @return array
     */
    private function get_possible_admins(group $group): array {
        global $USER;

        $groupmembers = $group->groupmembers;
        $groupmembers = array_filter($groupmembers, static function($member) use ($USER) {
            return $member->id !== $USER->id;
        });
        $possibleadmins = [];
        foreach ($groupmembers as $member) {
            $possibleadmins[$member->id] = fullname($member);
        }

        return $possibleadmins;
    }

    /**
     * returns the context required for dynamic submission
     * @return context
     * @throws \dml_exception
     */
    protected function get_context_for_dynamic_submission(): context {
        return \context_system::instance();
    }

    /**
     * checks access for dynamic submission
     * @return void
     */
    protected function check_access_for_dynamic_submission(): void {

    }

    /**
     * processes the dynamic submission
     * @return mixed|void
     * @throws \dml_exception
     */
    public function process_dynamic_submission() {
        global $USER;

        $groupid = $this->_ajaxformdata['groupId'];
        $newadminid = $this->_ajaxformdata['newAdmin'];

        groups::make_admin($newadminid, $groupid);
        groups::leave_group($USER->id, $groupid);
    }

    /**
     * sets data for dynamic submission
     * @return void
     */
    public function set_data_for_dynamic_submission(): void {
        // Workaround, because construct is final.
        // Get the group here, so we don`t need to get it everywhere.
        $this->group = groups::get_group_by_id($this->_ajaxformdata['groupId']);
    }

    /**
     * returns page url for dynamic submission
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        return new moodle_url('/local/thi_learning_companions/group/search.php');
    }

    /**
     * validates the form data
     * @param $data
     * @param $files
     * @return array
     * @throws \coding_exception
     */
    public function validation($data, $files) {
        global $USER;
        $errors = parent::validation($data, $files);

        $userisadmin = $this->group->is_user_admin($USER->id);
        if (!$userisadmin) {
            $errors['newAdmin'] = get_string('user_is_not_group_admin', 'local_thi_learning_companions');
        }

        $adminisset = array_key_exists('newAdmin', $data);
        if (!$adminisset) {
            $newadminisuserofgroup = $this->group->is_user_member($data['newAdmin']);
            if (!$newadminisuserofgroup) {
                $errors['newAdmin'] = get_string('new_admin_is_not_member', 'local_thi_learning_companions');
            }
        }

        return $errors;
    }
}
