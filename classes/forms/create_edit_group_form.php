<?php

namespace local_thi_learning_companions\forms;

use local_thi_learning_companions\group;
use local_thi_learning_companions\groups;
use tool_brickfield\local\areas\mod_choice\option;

require_once $CFG->libdir . "/formslib.php";

class create_edit_group_form extends \moodleform {
    public function __construct($action = null, $customdata = null, $method = 'post', $target = '', $attributes = null, $editable = true, $ajaxformdata = null) {
        // Automatically add the class 'thi_learning_companions_group_form' to the form.
        $attributes['class'] = array_merge($attributes['class'] ?? [], ['thi_learning_companions_group_form']);
        parent::__construct($action, $customdata, $method, $target, ['class' => 'thi_learning_companions_group_form'], $editable, $ajaxformdata);
    }

    public static function get_filepickeroptions() {
        $config = get_config('local_thi_learning_companions');
        return [
            'maxfiles' => 1,
            'accepted_types' => ['web_image'],
            'maxbytes' => $config->groupimage_maxbytes
        ];
    }

    protected function definition() {
        global $CFG;
        $mform = $this->_form;
        $topicchoices = groups::get_available_topics();

        $referrer = optional_param('referrer', '', PARAM_TEXT);

        $mform->addElement('hidden', 'referrer', $referrer);

        // ####### GROUP NAME
        $mform->addElement('text', 'name', get_string('groupname', 'local_thi_learning_companions'), 'size="60" maxlength="100"');
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required');
        $mform->addRule('name', get_string('maxlengthwarning', 'local_thi_learning_companions', 4000), 'maxlength', 100);

        // ############### GROUP KEYWORDS
        $knownkeywords = \local_thi_learning_companions\groups::get_all_keywords();
        $knownkeywords = array_combine($knownkeywords, $knownkeywords);
        $options = [
            'multiple' => true, 'noselectionstring' => get_string('nokeywords', 'local_thi_learning_companions'),
            'tags' => true
        ];
        $mform->addElement('autocomplete', 'keywords', get_string('group_topic', 'local_thi_learning_companions'), $knownkeywords, $options);
        $mform->addHelpButton('keywords', 'keywords', 'local_thi_learning_companions');

        // ########### GROUP COURSE CONTEXT
        $systemcontext = \context_system::instance();
        $limittoenrolled = !has_capability('moodle/category:viewcourselist', $systemcontext);
        $coursecontextoptions = array(
            'limittoenrolled' => $limittoenrolled,
            'multiple' => false,
            'includefrontpage' => false
        );
        $mform->addElement('course', 'courseid', get_string('coursecontext', 'local_thi_learning_companions'), $coursecontextoptions);
        if (isset($this->_customdata['courseid']) && !empty($this->_customdata['courseid'])) {
            $mform->setDefault('courseid', intval($this->_customdata['courseid']));
        }
        $mform->addHelpButton('courseid', 'coursecontext', 'local_thi_learning_companions');

        // ############### GROUP LEARNING NUGGET CONTEXT
        $nuggetcontextoptions = array(
            'ajax' => 'local_thi_learning_companions/nuggetcontext',
            'valuehtmlcallback' => function($value) {
                global $CFG;
                require_once $CFG->dirroot . "/question/editlib.php";
                $cm = get_module_from_cmid($value);
                return $cm[0]->name;
            }
        );
        $mform->addElement('autocomplete', 'cmid', get_string('nuggetcontext', 'local_thi_learning_companions'), null, $nuggetcontextoptions);
        if (isset($this->_customdata['cmid']) && !empty($this->_customdata['cmid'])) {
            $mform->setDefault('cmid', array((int)$this->_customdata['cmid']));
        }
        $mform->addHelpButton('cmid', 'nuggetcontext', 'local_thi_learning_companions');
        $mform->disabledIf('cmid', 'courseid', 'eq', ''); //, 'noitemselected');


        // ############## GROUP OPEN/CLOSED
        $mform->addElement('select', 'closedgroup', get_string('closedgroup', 'local_thi_learning_companions'), [
            0 => get_string('no'),
            1 => get_string('yes')
        ]);
        $mform->setDefault('closedgroup', 0);
        $mform->addHelpButton('closedgroup', 'closedgroup', 'local_thi_learning_companions');

        // ############## COURSE IMAGE
        $filepickerOptions = self::get_filepickeroptions();
        $mform->addElement('filepicker', 'groupimage', get_string('group_image', 'local_thi_learning_companions'), null, $filepickerOptions);
        $context = \context_system::instance();
        $draftitemid = file_get_submitted_draft_itemid('groupimage');
        if (empty($entry->id)) {
            $entry = new \stdClass;
            $entry->id = null;
        }
        file_prepare_draft_area($draftitemid, $context->id, 'mod_glossary', 'groupimage', $entry->id, self::get_filepickeroptions());

        $mform->addElement('editor', 'description_editor', get_string('group_description', 'local_thi_learning_companions'));
        //        $mform->setType('description', PARAM_RAW);
        //        $mform->addRule('description', get_string('required'), 'required');

        // ############# HIDDEN DATA
        if (isset($this->_customdata['groupid'])) {
            // ICTODO: check that the cmid belongs to the given course id (see below)
            // and that the user has the right to create a group for this course module
            // btw. might become redundant. we probably won't have groups that belong to a course module
            $groupid = $this->_customdata['groupid'];
        } else {
            $groupid = 0;
        }
        $mform->addElement('hidden', 'groupid', $groupid);
        $mform->setType('groupid', PARAM_INT);

        $this->add_action_buttons();
    }

    public function setGroupData(group $group) {
        $this->set_data([
            'name' => $group->name,
            'description_editor' => [
                'text' => $group->description,
                'format' => FORMAT_HTML
            ],
            'closedgroup' => $group->closedgroup,
            'keywords' => $group->keywords,
            'courseid' => $group->cmid,
            'groupimage' => $group->image
        ]);
    }

}
