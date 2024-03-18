<?php
namespace local_thi_learning_companions;
global $CFG;
require_once $CFG->libdir . DIRECTORY_SEPARATOR . 'formslib.php';
require_once $CFG->dirroot . DIRECTORY_SEPARATOR . 'repository' . DIRECTORY_SEPARATOR . 'lib.php';
require_once __DIR__ . '/lccustomeditor.php';
class chat_post_form extends \moodleform {

    public function __construct($action = null, $customdata = null, $method = 'post', $target = '', $attributes = null, $editable = true, $ajaxformdata = null) {
        $attributes['class'] = 'chat-post-form';
        parent::__construct($action, $customdata, $method, $target, $attributes, $editable, $ajaxformdata);
    }

    /**
     * Returns the options array to use in filemanager for chat attachments
     *
     * @return array
     */
    public static function attachment_options() {
        global $PAGE, $CFG;
        $maxbytes = self::get_upload_size_limit();
//        $maxbytes = get_user_max_upload_file_size($PAGE->context, $CFG->maxbytes);
        return array(
            'subdirs' => 0,
            'maxbytes' => $maxbytes,
            'areamaxbytes' => $maxbytes,
            'maxfiles' => 3,
            'accepted_types' => '*',
            'return_types' => FILE_INTERNAL | FILE_CONTROLLED_LINK
        );
    }

    /**
     * @return float|int
     * @throws \dml_exception
     */
    public static function get_upload_size_limit() {
        global $CFG;
        require_once $CFG->dirroot . '/lib/setuplib.php';
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
        global $PAGE, $CFG;
        $context = \context_system::instance();
        $maxbytes = self::get_upload_size_limit();
//        $maxbytes = get_user_max_upload_file_size($PAGE->context, $CFG->maxbytes, $CFG->maxbytes);
        return [
            'rows' => '5',
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'maxbytes' => $maxbytes,
            'trusttext'=> true,
            'autosave' => false,
            'enable_filemanagement' => true,
            'return_types'=> FILE_INTERNAL | FILE_EXTERNAL,
            'subdirs' => file_area_contains_subdirs($context, 'local_thi_learning_companions', 'message', $postid),
            'atto:toolbar' => 'collapse = collapse
style1 = title, bold, italic, image
style2 = underline, strike'
        ];
    }

    function definition() {
        global $OUTPUT, $DB;

        $mform =& $this->_form;
        $mform->disable_form_change_checker();
        $groupId = optional_param('groupid', null, PARAM_INT);
        //if the group is closed, use the "request to join" string, otherwise use the "join group" string
        $joinGroupString = get_string('join_group_link_text', 'local_thi_learning_companions');
        if ($DB->get_record('thi_lc_groups', ['id' => $groupId])->closedgroup) {
            $joinGroupString = get_string('request_join_group', 'local_thi_learning_companions');
        }

        $chatid = $this->_customdata['chatid'];

        $mform->addElement('lccustomeditor', 'message', get_string('message', 'local_thi_learning_companions'), ['rows' => 5], self::editor_options((empty($chatid) ? null : $chatid)));
        $mform->setType('message', PARAM_RAW);
        $mform->addRule('message', get_string('required'), 'required', null, 'client');

        $mform->addElement('html', '<div class="js-chat-preview preview-wrapper flex-column p-6 align-items-center d-none">
        <span class="preview-text">' . get_string('previewing_group', 'local_thi_learning_companions') . '</span>' .
            '<a href="/local/thi_learning_companions/join.php?groupid=' . $groupId . '" class="btn btn-primary preview-link">' . $joinGroupString . '</a></div>');

        $mform->addElement('filemanager', 'attachments', get_string('attachment', 'local_thi_learning_companions'), null,
            self::attachment_options());
        $mform->addHelpButton('attachments', 'attachment', 'local_thi_learning_companions');

        $mform->addElement('hidden', 'chatid');
        if (isset($chatid)) {
            $mform->setDefault('chatid', $chatid);
        }
        $mform->setType('chatid', PARAM_INT);

        $mform->addElement('html', '<span id="local_thi_learning_companions_chat-send" class="btn btn-primary">' . get_string('send', 'local_thi_learning_companions') . '</span>');
    }

    /**
     * Form validation
     *
     * @param array $data data from the form.
     * @param array $files files uploaded.
     * @return array of errors.
     */
    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (empty($data['message']['text'])) {
            $errors['message'] = get_string('erroremptymessage', 'local_thi_learning_companions');
        }
        return $errors;
    }
}
