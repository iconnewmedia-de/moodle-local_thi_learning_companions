<?php
namespace local_learningcompanions;
global $CFG;
require_once $CFG->libdir . DIRECTORY_SEPARATOR . 'formslib.php';
require_once $CFG->dirroot . DIRECTORY_SEPARATOR . 'repository' . DIRECTORY_SEPARATOR . 'lib.php';
require_once __DIR__ . '/lccustomeditor.php';
class chat_post_form extends \moodleform {

    /**
     * Returns the options array to use in filemanager for chat attachments
     *
     * @return array
     */
    public static function attachment_options() {
        global $PAGE, $CFG;
        $maxbytes = get_user_max_upload_file_size($PAGE->context, $CFG->maxbytes);
        return array(
            'subdirs' => 0,
            'maxbytes' => $maxbytes,
            'maxfiles' => 3,
            'accepted_types' => '*',
            'return_types' => FILE_INTERNAL | FILE_CONTROLLED_LINK
        );
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
        $maxbytes = get_user_max_upload_file_size($PAGE->context, $CFG->maxbytes, $CFG->maxbytes);
        return array(
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'maxbytes' => $maxbytes,
            'trusttext'=> true,
            'return_types'=> FILE_INTERNAL | FILE_EXTERNAL,
            'subdirs' => file_area_contains_subdirs($context, 'local_learningcompanions', 'message', $postid),
            /*'atto:toolbar' => 'collapse = collapse
style1 = title, bold, italic
list = unorderedlist, orderedlist, indent
links = link
files = emojipicker, image, media, recordrtc, managefiles
style2 = underline, strike, subscript, superscript
align = align
undo = undo'*/
        );
    }

    function definition() {
        global $OUTPUT;

        $mform =& $this->_form;
        $mform->disable_form_change_checker();

        $chatid = $this->_customdata['chatid'];

//        $mform->addElement('lccustomeditor', 'message', get_string('message', 'local_learningcompanions'), null, self::editor_options((empty($chatid) ? null : $chatid)));
        $mform->addElement('editor', 'message', get_string('message', 'local_learningcompanions'), null, self::editor_options((empty($chatid) ? null : $chatid)));
        $mform->setType('message', PARAM_RAW);
        $mform->addRule('message', get_string('required'), 'required', null, 'client');

        $mform->addElement('html', '<div class="js-chat-preview preview-wrapper d-flex flex-column p-6 align-items-center">
        <span class="preview-text">' . get_string('previewing_group', 'local_learningcompanions') . '</span>' .
            '<a href="/local/learningcompanions/join.php?groupid='.optional_param('groupid', null, PARAM_INT).'" class="preview-link">' . get_string('join_group_link_text', 'local_learningcompanions') . '</a></div>');

        $mform->addElement('filemanager', 'attachments', get_string('attachment', 'local_learningcompanions'), null,
            self::attachment_options());
        $mform->addHelpButton('attachments', 'attachment', 'local_learningcompanions');

        $mform->addElement('hidden', 'chatid');
        if (isset($chatid)) {
            $mform->setDefault('chatid', $chatid);
        }
        $mform->setType('chatid', PARAM_INT);

        $mform->addElement('html', '<span id="local_learningcompanions_chat-send" class="btn btn-primary">' . get_string('send', 'local_learningcompanions') . '</span>');
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
            $errors['message'] = get_string('erroremptymessage', 'local_learningcompanions');
        }
        return $errors;
    }
}
