<?php
namespace local_learningcompanions;

class chat {
    protected $chatid;
    protected $chat;
    protected $context;
    public function __construct($groupid) {
        global $DB;
        $this->groupid = $groupid;
        $this->chat = $DB->get_record('lc_chat', array('relatedid' => $groupid, 'chattype' => 1));
        if (!$this->chat) {
            $chat = new \stdClass();
            $chat->chattype = 1;
            $chat->relatedid = $groupid;
            $chat->timecreated = time();
            $group = $DB->get_record('lc_groups', array('id' => $groupid));
            $chat->course = $group->courseid;
            $chatid = $DB->insert_record('lc_chat', $chat);
            $this->chat = $DB->get_record('lc_chat', array('id' => $chatid));
        }
        $this->chatid = $this->chat->id;
        $this->context = \context_system::instance();
        $this->filestorage = get_file_storage();
    }

    /**
     * @deprecated do not use! Use chats::post_comment instead!
     * @param $comment
     * @param $attachments
     * @return void
     * @throws \dml_exception
     */
    public function add_comment($comment, $attachments = []) {
        global $DB, $USER;
        // ICTODO: check if user has the permission to post to this chat
        $obj = new \stdClass();
        $obj->chatid = $this->chatid;
        $obj->userid = $USER->id;
        $obj->comment = $comment; // ICTODO: sanitize this! prevent XSS and stuff!
        $obj->flagged = 0;
        $obj->totalscore = 0;
        $obj->timecreated = time();
        $obj->timemodified = 0;
        $DB->insert_record('lc_chat_comment', $obj);
        // ICTODO: save attachments
    }

    public function update_comment($comment, $attachments) {
        // ICTODO: update comment and attachments
    }

    public function get_comments() {
        global $DB;
        $comments = $DB->get_records('lc_chat_comment', array('chatid' => $this->chatid), 'timecreated');
        $attachments = $this->get_attachments_of_comments($comments, 'attachments');
        $context = \context_system::instance();
        // ICTODO: also get inline attachments
        foreach($comments as $comment) {
            $comment->datetime = userdate($comment->timecreated);
            $comment->author = $DB->get_record('user', array('id' => $comment->userid), 'id,firstname,lastname,email,username');
            $comment->comment = file_rewrite_pluginfile_urls($comment->comment, 'pluginfile.php', $context->id, 'local_learningcompanions', 'message', $comment->id);
            if (array_key_exists($comment->id, $attachments)) {
                $comment->attachments = $attachments[$comment->id];
            } else {
                $comment->attachments = [];
            }
        }
        return $comments;
    }

    public function set_latestviewedcomment(int $chatid) {
        global $DB, $USER;
        $record = $DB->get_record('lc_chat_lastvisited', array('chatid' => $chatid, 'userid' => $USER->id));
        if ($record) {
            $record->timevisited = time();
            $DB->update_record('lc_chat_lastvisited', $record);
            return;
        }
        $record = new \stdClass();
        $record->userid = $USER->id;
        $record->chatid = $chatid;
        $record->timevisited = time();
        $DB->insert_record('lc_chat_lastvisited', $record);
    }

    public function get_attachments_of_comments(array $comments, string $area) {
        global $CFG;
        require_once($CFG->dirroot.'/local/learningcompanions/lib.php');
        return local_learningcompanions_get_attachments_of_chat_comments($comments, $area);
    }

    /**
     * returns the HTML and JS inclusions for the chat module to include on the chat page
     * @return mixed
     */
    public function get_chat_module() {
        global $USER, $OUTPUT, $CFG;
        $reactscript = \local_learningcompanions\get_chat_reactscript_path();
        $form = $this->get_submission_form(['chatid' => $this->chatid]);
        $context = array(
            'userid' => $USER->id,
            'reactscript' => $reactscript,
            'chatid' => $this->chatid,
            'groupid' => $this->groupid,
            'form' => $form
        );
        return $OUTPUT->render_from_template('local_learningcompanions/chat', $context);
    }

    protected function get_submission_form($customdata)
    {
        global $USER;
        require_once(__DIR__. "/chat_post_form.php");
        // ICTODO: dynamically get the course and module from the currently selected group

        $form = new \local_learningcompanions\chat_post_form(null, $customdata);
        $draftitemid = file_get_submitted_draft_itemid('attachments');
        $chatid = empty($customdata["chatid"]) ? null : $customdata["chatid"];
        $postid = empty($customdata["postid"]) ? null : $customdata["postid"];
        $attachoptions = \local_learningcompanions\chat_post_form::attachment_options();
        $context = \context_system::instance();
        file_prepare_draft_area($draftitemid, $context, 'local_learningcompanions', 'attachments', $postid, $attachoptions);
        $draftideditor = file_get_submitted_draft_itemid('message');

        $form->set_data(
            array(
                'attachments' => $draftitemid,
                'subject' => '',
                'message' => array(
                    'text' => '',
                    'format' => editors_get_preferred_format(),
                    'itemid' => $draftideditor
                ),
                'chatid' => $chatid
            )
        );
        $output = $form->render();
        $output = str_replace("col-md-3", "d-none", $output);
        $output = str_replace("col-form-label d-flex", "col-form-label", $output);
        $output = str_replace("col-md-9", "", $output);
        return $output;
    }

}
