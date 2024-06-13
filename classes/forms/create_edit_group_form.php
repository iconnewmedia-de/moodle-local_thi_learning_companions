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
use local_thi_learning_companions\group;
use local_thi_learning_companions\groups;
use tool_brickfield\local\areas\mod_choice\option;

require_once($CFG->libdir . "/formslib.php");

/**
 * Form for creating a new group
 */
class create_edit_group_form extends \moodleform {
    /**
     * Constructor (duh - comment just here for code checker)
     * @param mixed $action
     * @param mixed $customdata
     * @param string $method
     * @param string $target
     * @param mixed $attributes
     * @param bool $editable
     * @param array $ajaxformdata
     */
    public function __construct($action = null,
                                $customdata = null,
                                $method = 'post',
                                $target = '',
                                $attributes = null,
                                $editable = true,
                                $ajaxformdata = null
    ) {
        // Automatically add the class 'thi_learning_companions_group_form' to the form.
        $attributes['class'] = array_merge($attributes['class'] ?? [], ['thi_learning_companions_group_form']);
        parent::__construct($action,
            $customdata,
            $method,
            $target,
            ['class' => 'thi_learning_companions_group_form'],
            $editable,
            $ajaxformdata);
    }

    /**
     * returns the file picker options
     * @return array
     * @throws \dml_exception
     */
    public static function get_filepickeroptions() {
        $config = get_config('local_thi_learning_companions');
        return [
            'maxfiles' => 1,
            'accepted_types' => ['web_image'],
            'maxbytes' => $config->groupimage_maxbytes,
        ];
    }

    /**
     * the actual form definition
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    protected function definition() {
        $mform = $this->_form;

        $referrer = optional_param('referrer', '', PARAM_TEXT);

        $mform->addElement('hidden', 'referrer', $referrer);
        $mform->setType('referrer', PARAM_TEXT);

        // GROUP NAME.
        $mform->addElement('text', 'name', get_string('groupname', 'local_thi_learning_companions'), 'size="60" maxlength="100"');
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required');
        $mform->addRule('name', get_string('maxlengthwarning', 'local_thi_learning_companions', 4000), 'maxlength', 100);

        // GROUP KEYWORDS.
        $knownkeywords = \local_thi_learning_companions\groups::get_all_keywords();
        $knownkeywords = array_combine($knownkeywords, $knownkeywords);
        $options = [
            'multiple' => true, 'noselectionstring' => get_string('nokeywords', 'local_thi_learning_companions'),
            'tags' => true,
        ];
        $mform->addElement('autocomplete',
            'keywords',
            get_string('group_topic', 'local_thi_learning_companions'),
            $knownkeywords,
            $options
        );
        $mform->addHelpButton('keywords', 'keywords', 'local_thi_learning_companions');

        // GROUP COURSE CONTEXT.
        $systemcontext = \context_system::instance();
        $limittoenrolled = !has_capability('moodle/category:viewcourselist', $systemcontext);
        $coursecontextoptions = [
            'limittoenrolled' => $limittoenrolled,
            'multiple' => false,
            'includefrontpage' => false,
        ];
        $mform->addElement('course',
            'courseid',
            get_string('coursecontext', 'local_thi_learning_companions'),
            $coursecontextoptions);
        if (isset($this->_customdata['courseid']) && !empty($this->_customdata['courseid'])) {
            $mform->setDefault('courseid', intval($this->_customdata['courseid']));
        }
        $mform->addHelpButton('courseid', 'coursecontext', 'local_thi_learning_companions');

        // GROUP LEARNING NUGGET CONTEXT.
        $nuggetcontextoptions = [
            'ajax' => 'local_thi_learning_companions/nuggetcontext',
            'valuehtmlcallback' => function($value) {
                global $CFG;
                require_once($CFG->dirroot . "/question/editlib.php");
                $cm = get_module_from_cmid($value);
                return $cm[0]->name;
            },
        ];
        $mform->addElement('autocomplete',
            'cmid',
            get_string('nuggetcontext', 'local_thi_learning_companions'),
            null,
            $nuggetcontextoptions);
        if (isset($this->_customdata['cmid']) && !empty($this->_customdata['cmid'])) {
            $mform->setDefault('cmid', [(int)$this->_customdata['cmid']]);
        }
        $mform->addHelpButton('cmid', 'nuggetcontext', 'local_thi_learning_companions');
        $mform->disabledIf('cmid', 'courseid', 'eq', '');

        // GROUP OPEN/CLOSED.
        $mform->addElement('select', 'closedgroup', get_string('closedgroup', 'local_thi_learning_companions'), [
            0 => get_string('no'),
            1 => get_string('yes'),
        ]);
        $mform->setDefault('closedgroup', 0);
        $mform->addHelpButton('closedgroup', 'closedgroup', 'local_thi_learning_companions');

        // COURSE IMAGE.
        $filepickeroptions = self::get_filepickeroptions();
        $mform->addElement(
            'filepicker',
            'groupimage',
            get_string('group_image', 'local_thi_learning_companions'),
            null,
            $filepickeroptions
        );
        $context = \context_system::instance();
        $draftitemid = file_get_submitted_draft_itemid('groupimage');
        if (empty($entry->id)) {
            $entry = new \stdClass;
            $entry->id = null;
        }
        file_prepare_draft_area($draftitemid,
            $context->id,
          'mod_glossary',
            'groupimage',
            $entry->id,
        self::get_filepickeroptions());

        $mform->addElement('editor', 'description_editor', get_string('group_description', 'local_thi_learning_companions'));

        // HIDDEN DATA.
        if (isset($this->_customdata['groupid'])) {
            // ICTODO: check that the cmid belongs to the given course id (see below)
            // and that the user has the right to create a group for this course module
            // btw. might become redundant. we probably won't have groups that belong to a course module.
            $groupid = $this->_customdata['groupid'];
        } else {
            $groupid = 0;
        }
        $mform->addElement('hidden', 'groupid', $groupid);
        $mform->setType('groupid', PARAM_INT);

        $this->add_action_buttons();
    }

    /**
     * Sets the group data
     * @param group $group
     * @return void
     */
    public function set_group_data(group $group) {
        $this->set_data([
            'name' => $group->name,
            'description_editor' => [
                'text' => $group->description,
                'format' => FORMAT_HTML,
            ],
            'closedgroup' => $group->closedgroup,
            'keywords' => $group->keywords,
            'courseid' => $group->cmid,
            'groupimage' => $group->image,
        ]);
    }

}
