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

use context;
use local_thi_learning_companions\groups;
use moodle_url;

class last_user_leaves_closed_group_form extends \core_form\dynamic_form {

    /**
     * @inheritDoc
     */
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'groupId', $this->_ajaxformdata['groupId']);
        $mform->addElement('html', get_string('last_user_leaves_closed_group_description', 'local_thi_learning_companions'));
        $this->add_action_buttons(true, get_string('leave_group', 'local_thi_learning_companions'));
    }

    /**
     * @inheritDoc
     */
    protected function get_context_for_dynamic_submission(): context {
        return \context_system::instance();
    }

    /**
     * @inheritDoc
     */
    protected function check_access_for_dynamic_submission(): void {

    }

    /**
     * @inheritDoc
     */
    public function process_dynamic_submission() {
        global $USER;

        $groupid = $this->_ajaxformdata['groupId'];

        groups::leave_group($USER->id, $groupid);
    }

    /**
     * @inheritDoc
     */
    public function set_data_for_dynamic_submission(): void {
    }

    /**
     * @inheritDoc
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        return new moodle_url('/local/thi_learning_companions/group/search.php');
    }
}
