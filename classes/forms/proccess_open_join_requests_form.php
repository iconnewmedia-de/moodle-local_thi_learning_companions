<?php

namespace local_thi_learning_companions\forms;
global $CFG;

use local_thi_learning_companions\groups;

require_once $CFG->libdir . "/formslib.php";


class proccess_open_join_requests_form extends \moodleform {

    /**
     * @inheritDoc
     */
    protected function definition() {
        global $DB;
        $openRequests = \local_thi_learning_companions\groups::get_group_join_requests();

        $form = $this->_form;

        if (!count($openRequests)) {
            $form->addElement('html', '<p>' . get_string('no_open_requests', 'local_thi_learning_companions') . '</p>');
            return;
        }

        foreach ($openRequests as $request) {
            $groupname = $DB->get_field('thi_lc_groups', 'name', array('id' => $request->groupid));
            $username = $request->user->firstname . ' ' . $request->user->lastname . ' (' . $request->user->email . ')';
            $form->addElement('static', 'request_' . $request->id,
                get_string('groupjoin_request_group', 'local_thi_learning_companions', $groupname),
                get_string('groupjoin_request_user', 'local_thi_learning_companions',$username)
            );
            $form->addElement('radio', 'request_' . $request->id . '_action', '', 'Accept', 'accept');
            $form->addElement('radio', 'request_' . $request->id . '_action', '', 'Decline', 'decline');
        }

        $this->add_action_buttons(false, get_string('process_requests', 'local_thi_learning_companions'));
    }
}
