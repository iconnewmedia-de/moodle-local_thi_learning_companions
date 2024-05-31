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
namespace local_thi_learning_companions;
defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . DIRECTORY_SEPARATOR . 'formslib.php');
require_once($CFG->dirroot . DIRECTORY_SEPARATOR . 'repository' . DIRECTORY_SEPARATOR . 'lib.php');
require_once(__DIR__ . '/lccustomeditor.php');

/**
 *
 */
class chat_post_form extends \moodleform {

    /**
     * @param $action
     * @param $customdata
     * @param $method
     * @param $target
     * @param $attributes
     * @param $editable
     * @param $ajaxformdata
     */
    public function __construct(
        $action = null,
        $customdata = null,
        $method = 'post',
        $target = '',
        $attributes = null,
        $editable = true,
        $ajaxformdata = null
    ) {
        $attributes['class'] = 'chat-post-form';
        parent::__construct($action, $customdata, $method, $target, $attributes, $editable, $ajaxformdata);
    }

    /**
     * Returns the options array to use in filemanager for chat attachments
     *
     * @return array
     */
    public static function attachment_options() {
        $maxbytes = self::get_upload_size_limit();
        return [
            'subdirs' => 0,
            'maxbytes' => $maxbytes,
            'areamaxbytes' => $maxbytes,
            'maxfiles' => 3,
            'accepted_types' => '*',
            'return_types' => FILE_INTERNAL | FILE_CONTROLLED_LINK,
        ];
    }

    /**
     * @return float|int
     * @throws \dml_exception
     */
    public static function get_upload_size_limit() {
        global $CFG;
        require_once($CFG->dirroot . '/lib/setuplib.php');
        $config = get_config('local_thi_learning_companions');
        $maxbytes = intval($config->upload_limit_per_message) . "M";
        $maxbytes = get_real_size($maxbytes);
        return $maxbytes;
    }

    /**
     * Returns the options array to use in chat text editor
     *
     * @param int $postid post id, use null when adding new post
     * @return array
     */
    public static function editor_options($postid) {
        $context = \context_system::instance();
        $maxbytes = self::get_upload_size_limit();
        return [
            'rows' => '5',
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'maxbytes' => $maxbytes,
            'trusttext' => true,
            'autosave' => false,
            'enable_filemanagement' => true,
            'return_types' => FILE_INTERNAL | FILE_EXTERNAL,
            'subdirs' => file_area_contains_subdirs($context, 'local_thi_learning_companions', 'message', $postid),
            'atto:toolbar' => 'collapse = collapse
style1 = title, bold, italic, image
style2 = underline, strike',
        ];
    }

    /**
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function definition() {
        global $OUTPUT, $DB;

        $mform =& $this->_form;
        $mform->disable_form_change_checker();
        $groupid = optional_param('groupid', null, PARAM_INT);
        // If the group is closed, use the "request to join" string, otherwise use the "join group" string.
        $joingroupstring = get_string('join_group_link_text', 'local_thi_learning_companions');
        if ($groupid && $DB->get_record('thi_lc_groups', ['id' => $groupid])->closedgroup) {
            $joingroupstring = get_string('request_join_group', 'local_thi_learning_companions');
        }

        $chatid = $this->_customdata['chatid'];

        $mform->addElement(
            'lccustomeditor',
            'message',
            get_string('message', 'local_thi_learning_companions'),
            ['rows' => 5],
            self::editor_options((empty($chatid) ? null : $chatid))
        );
        $mform->setType('message', PARAM_RAW);
        $mform->addRule('message', get_string('required'), 'required', null, 'client');

        $mform->addElement(
            'html',
            '<div class="js-chat-preview preview-wrapper flex-column p-6 align-items-center d-none">
        <span class="preview-text">' . get_string('previewing_group', 'local_thi_learning_companions') .
            '</span>' .
            '<a href="/local/thi_learning_companions/join.php?groupid=' . $groupid . '" class="btn btn-primary preview-link">' .
            $joingroupstring . '</a></div>'
        );

        $mform->addElement('filemanager', 'attachments', get_string('attachment', 'local_thi_learning_companions'), null,
            self::attachment_options());
        $mform->addHelpButton('attachments', 'attachment', 'local_thi_learning_companions');

        $mform->addElement('hidden', 'chatid');
        if (isset($chatid)) {
            $mform->setDefault('chatid', $chatid);
        }
        $mform->setType('chatid', PARAM_INT);
        // Needed for CSS - depending on the active editor we might need to position the send button a little lower.
        $activetexteditor = $this->get_active_texteditor();
        $mform->addElement(
            'html',
            '<span id="local_thi_learning_companions_chat-send" class="thi_learning_companions_chat_editor_' .
            $activetexteditor . ' btn btn-primary">' .
            get_string('send', 'local_thi_learning_companions') . '</span>'
        );
    }

    /**
     * returns the name of the text editor that is currently being used (ATTO, TinyMCE, ...)
     * @return string|void
     */
    protected function get_active_texteditor() {
        global $CFG;
        if (empty($CFG->texteditors)) {
            $CFG->texteditors = 'atto,tiny,textarea';
        }
        $active = explode(',', $CFG->texteditors);

        foreach ($active as $editorname) {
            if (get_texteditor($editorname)) {
                return $editorname;
            }
        }
    }

    /**
     * Form validation
     *
     * @param array $data data from the form.
     * @param array $files files uploaded.
     * @return array of errors.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (empty($data['message']['text'])) {
            $errors['message'] = get_string('erroremptymessage', 'local_thi_learning_companions');
        }
        return $errors;
    }
}
