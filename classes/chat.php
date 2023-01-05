<?php
namespace local_learningcompanions;

class chat {
    protected $chatid;
    protected $chat;
    protected $context;

    public function __construct($groupid) {
        global $DB;
        $this->groupid = $groupid;
        $this->chat = $DB->get_record('lc_chat', ['relatedid' => $groupid, 'chattype' => 1]);
        if (!$this->chat) {
            $chat = new \stdClass();
            $chat->chattype = 1;
            $chat->relatedid = $groupid;
            $chat->timecreated = time();
            $group = $DB->get_record('lc_groups', ['id' => $groupid]);
            $chat->course = $group->courseid;
            $chatid = $DB->insert_record('lc_chat', $chat);
            $this->chat = $DB->get_record('lc_chat', ['id' => $chatid]);
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

    /**
     * @param int $page the Page to use, used for pagination
     * @param int $offset Any offset. Can be used, to compensate new posts, that were added in the meantime
     *
     * @return array
     * @throws \dml_exception
     */
    public function get_comments(int $page = 1, int $offset = 0) {
        global $DB;
        $stepSize = 5;
        $comments = $DB->get_records('lc_chat_comment', ['chatid' => $this->chatid], 'timecreated DESC', '*', ($page-1) * $stepSize + $offset, $stepSize);
        $attachments = $this->get_attachments_of_comments($comments, 'attachments');
        $context = \context_system::instance();
        // ICTODO: also get inline attachments
        foreach($comments as $comment) {
            $comment->datetime = userdate($comment->timecreated);
            $comment->author = $DB->get_record('user', ['id' => $comment->userid], 'id,firstname,lastname,email,username');
            $comment->comment = file_rewrite_pluginfile_urls($comment->comment, 'pluginfile.php', $context->id, 'local_learningcompanions', 'message', $comment->id);
            if (array_key_exists($comment->id, $attachments)) {
                $comment->attachments = $attachments[$comment->id];
            } else {
                $comment->attachments = [];
            }
        }
        return $comments;
    }

    public function get_newest_posts(int $lastViewedPostId) {
        global $DB;

        //Get the newest comments
        $comments = $DB->get_records_sql('SELECT * FROM {lc_chat_comment} WHERE chatid = ? AND id > ? ORDER BY timecreated DESC', [$this->chatid, $lastViewedPostId]);
        $this->set_latestviewedcomment($this->chatid);

        //Get the attachments
        $attachments = $this->get_attachments_of_comments($comments, 'attachments');
        $context = \context_system::instance();

        foreach($comments as $comment) {
            $comment->datetime = userdate($comment->timecreated);
            $comment->author = $DB->get_record('user', ['id' => $comment->userid], 'id,firstname,lastname,email,username');
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
        $record = $DB->get_record('lc_chat_lastvisited', ['chatid' => $chatid, 'userid' => $USER->id]);
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
        $context = [
            'userid' => $USER->id,
            'reactscript' => $reactscript,
            'chatid' => $this->chatid,
            'groupid' => $this->groupid,
            'form' => $form
        ];
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
            [
                'attachments' => $draftitemid,
                'subject' => '',
                'message' => [
                    'text' => '',
                    'format' => editors_get_preferred_format(),
                    'itemid' => $draftideditor
                ],
                'chatid' => $chatid
            ]
        );
        $output = $form->render();
        $output = str_replace(["col-md-3", "col-form-label d-flex", "col-md-9"], [
            "d-none", "col-form-label", ""
        ], $output);
        return $output;
    }

}
