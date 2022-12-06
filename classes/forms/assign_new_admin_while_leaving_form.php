<?php

namespace local_learningcompanions\forms;
global $CFG;

use context;
use core_form\dynamic_form;
use local_learningcompanions\group;
use local_learningcompanions\groups;
use moodle_url;

require_once $CFG->libdir . "/formslib.php";

class assign_new_admin_while_leaving_form extends dynamic_form {
    /**
     * @var group
     */
    private $group;

    /**
     * @inheritDoc
     */
    protected function definition() {
        $mform = $this->_form;

        $possibleAdmins = $this->getPossibleAdmins();

        $mform->addElement('static', 'description', get_string('assign_new_admin_while_leaving_description', 'local_learningcompanions'));
        $mform->addElement('hidden', 'groupId', $this->_ajaxformdata['groupId']);
        $mform->addElement('select', 'newAdmin', get_string('choose_new_admin', 'local_learningcompanions'), $possibleAdmins);
        $this->add_action_buttons(false, 'Leave group');
    }

    private function getPossibleAdmins(): array {
        global $USER;

        $groupid = $this->_ajaxformdata['groupId'];
        $group = \local_learningcompanions\groups::get_group_by_id($groupid);
        $groupmembers = $group->groupmembers;
        $groupmembers = array_filter($groupmembers, static function($member) use ($USER) {
            return $member->id !== $USER->id;
        });
        //ICTODO: the first user should be the last active user, because this user is the one, that gets shown the first
        $possibleAdmins = [];
        foreach ($groupmembers as $member) {
            $possibleAdmins[$member->id] = fullname($member);
        }

        return $possibleAdmins;
    }

    protected function get_context_for_dynamic_submission(): context {
        return \context_system::instance();
    }

    protected function check_access_for_dynamic_submission(): void {}

    public function process_dynamic_submission() {
        global $USER;

        $groupId = $this->_ajaxformdata['groupId'];
        $newAdminId = $this->_ajaxformdata['newAdmin'];

        groups::make_admin($newAdminId, $groupId);
        groups::leave_group($USER->id, $groupId);
    }

    public function set_data_for_dynamic_submission(): void {
        //Workaround, because construct is final
        //Get the group here, so we don`t need to get it everywhere
        $this->group = groups::get_group_by_id($this->_ajaxformdata['groupId']);
    }

    protected function get_page_url_for_dynamic_submission(): moodle_url {
        return new moodle_url('/local/learningcompanions/group/search.php');
    }

    public function validation($data, $files) {
        global $USER;
        $errors = parent::validation($data, $files);

        $userIsAdmin = $this->group->is_user_admin($USER->id);
        if (!$userIsAdmin) {
            $errors['newAdmin'] = get_string('user_is_not_group_admin', 'local_learningcompanions');
        }

        $adminIsSet = array_key_exists('newAdmin', $data);
        if (!$adminIsSet) {
            $newAdminIsUserOfGroup = $this->group->is_user_member($data['newAdmin']);
            if (!$newAdminIsUserOfGroup) {
                $errors['newAdmin'] = get_string('new_admin_is_not_member', 'local_learningcompanions');
            }
        }

        return $errors;
    }
}
