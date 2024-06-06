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
use local_thi_learning_companions\groups;

require_once($CFG->libdir . "/formslib.php");

/**
 * Form for processing the join request
 */
class proccess_open_join_requests_form extends \moodleform {

    /**
     * the form definition
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    protected function definition() {
        global $DB;
        $openrequests = \local_thi_learning_companions\groups::get_group_join_requests();

        $form = $this->_form;

        if (!count($openrequests)) {
            $form->addElement('html', '<p>' . get_string('no_open_requests', 'local_thi_learning_companions') . '</p>');
            return;
        }

        foreach ($openrequests as $request) {
            $groupname = $DB->get_field('thi_lc_groups', 'name', ['id' => $request->groupid]);
            $username = $request->user->firstname . ' ' . $request->user->lastname . ' (' . $request->user->email . ')';
            $form->addElement('static', 'request_' . $request->id,
                get_string('groupjoin_request_group', 'local_thi_learning_companions', $groupname),
                get_string('groupjoin_request_user', 'local_thi_learning_companions', $username)
            );
            $form->addElement('radio', 'request_' . $request->id . '_action', '', 'Accept', 'accept');
            $form->addElement('radio', 'request_' . $request->id . '_action', '', 'Decline', 'decline');
        }

        $this->add_action_buttons(false, get_string('process_requests', 'local_thi_learning_companions'));
    }
}
