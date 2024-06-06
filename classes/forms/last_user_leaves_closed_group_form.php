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

use context;
use local_thi_learning_companions\groups;
use moodle_url;

/**
 * Form that appears when the last user leaves a group, leaving it empty
 */
class last_user_leaves_closed_group_form extends \core_form\dynamic_form {

    /**
     * the form definition
     * @return void
     * @throws \coding_exception
     */
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'groupId', $this->_ajaxformdata['groupId']);
        $mform->addElement('html', get_string('last_user_leaves_closed_group_description', 'local_thi_learning_companions'));
        $this->add_action_buttons(true, get_string('leave_group', 'local_thi_learning_companions'));
    }

    /**
     * returns the context for dynamic form submission
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

        groups::leave_group($USER->id, $groupid);
    }

    /**
     * sets data for dynamic submission
     * @return void
     */
    public function set_data_for_dynamic_submission(): void {
    }

    /**
     * returns page url for dynamic submission
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        return new moodle_url('/local/thi_learning_companions/group/search.php');
    }
}
