<?php

namespace local_learningcompanions\forms;
global $CFG;

use local_learningcompanions\groups;

require_once $CFG->libdir . "/formslib.php";


class proccess_open_join_requests_form extends \moodleform {

    /**
     * @inheritDoc
     */
    protected function definition() {
        $openRequests = \local_learningcompanions\groups::get_group_join_requests();

        $form = $this->_form;

        if (!count($openRequests)) {
            $form->addElement('html', '<p>' . get_string('no_open_requests', 'local_learningcompanions') . '</p>');
            return;
        }

        foreach ($openRequests as $request) {
            $form->addElement('static', 'request_' . $request->id, $request->user->firstname . ' ' . $request->user->lastname . ' (' . $request->user->email . ')');
            $form->addElement('radio', 'request_' . $request->id . '_action', '', 'Accept', 'accept');
            $form->addElement('radio', 'request_' . $request->id . '_action', '', 'Decline', 'decline');
        }

        $this->add_action_buttons(false, get_string('process_requests', 'local_learningcompanions'));
    }
}
