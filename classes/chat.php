<?php
namespace local_learningcompanions;

class chat {
    protected $chatid;
    protected $chat;
    protected $context;

    public static function createGroupChat($groupid): self {
        global $DB;

        $new_chat = new self();
        $new_chat->groupid = $groupid;
        $new_chat->chat = $DB->get_record('lc_chat', ['relatedid' => $groupid, 'chattype' => 1]);
        if ($groupid) {
            if (!$new_chat->chat) {
                $chat = new \stdClass();
                $chat->chattype = 1;
                $chat->relatedid = $groupid;
                $chat->timecreated = time();
                $group = $DB->get_record('lc_groups', ['id' => $groupid]);
                $chat->course = $group->courseid;
                $chatid = $DB->insert_record('lc_chat', $chat);
                $new_chat->chat = $DB->get_record('lc_chat', ['id' => $chatid]);
            }
            $new_chat->chatid = $new_chat->chat->id;
        }
        $new_chat->context = \context_system::instance();
        $new_chat->filestorage = get_file_storage();
        return $new_chat;
    }

    public static function createQuestionChat($questionid): self {
        global $DB;

        $new_chat = new self();
        $new_chat->context = \context_system::instance();
        $new_chat->filestorage = get_file_storage();
        $new_chat->chat = $DB->get_record('lc_chat', ['relatedid' => $questionid, 'chattype' => groups::CHATTYPE_MENTOR]);

        if (!$new_chat->chat) {
            $chat = new \stdClass();
            $chat->chattype = groups::CHATTYPE_MENTOR;
            $chat->relatedid = $questionid;
            $chat->timecreated = time();
            $chat->course = 0;
            $chatid = $DB->insert_record('lc_chat', $chat);
            $new_chat->chat = $DB->get_record('lc_chat', ['id' => $chatid]);
        }
        $new_chat->chatid = $new_chat->chat->id;

        return $new_chat;
    }


    private function get_language_strings(): string {
        $stringKeys = [
            'delete_post',
            'report_post',
            'no_posts_available'
        ];
        $strings = get_strings($stringKeys, 'local_learningcompanions');
        $stringsString = json_encode($strings);
        return "M.str.local_learningcompanions = {...M.str.local_learningcompanions, ...$stringsString};";
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
    public function get_comments() {
        global $DB;
        $comments = $DB->get_records('lc_chat_comment', ['chatid' => $this->chatid], 'timecreated DESC');
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

    /**
     * @param int|null $firstPostId The last post id, that was already loaded. If null, the posts will be loaded from the beginning
     * @param int|null $includedPostId The id of the Post, that should be included in the result, even if it is older than $firstPostId
     *
     * @return array
     * @throws \dml_exception
     */
    public function get_posts_for_chat(int $firstPostId = null, int $includedPostId = 0) {
        global $DB;

        $stepSize = 5;

        if (is_null($firstPostId)) {
            $firstPostId = $DB->get_field_sql('SELECT MAX(id) FROM {lc_chat_comment} WHERE chatid = ?', [$this->chatid]);
            $firstPostId++;
        }

        $sql = 'SELECT c.*, GROUP_CONCAT(r.userid) as ratings
                    FROM {lc_chat_comment} c
                    LEFT JOIN {lc_chat_comment_ratings} r ON r.commentid = c.id
                    WHERE c.chatid = :chatid AND c.id < :firstpostid ';
        $params = ['chatid' => $this->chatid, 'firstpostid' => $firstPostId];

        if ($includedPostId !== 0) {
            $sql .= ' AND c.id >= :includedpostid ';
            $params['includedpostid'] = $includedPostId;
        }

        $sql .= ' GROUP BY c.id ';
        $sql .= 'ORDER BY timecreated DESC ';
        if ($includedPostId === 0) {
            $sql .= ' LIMIT 0, '.$stepSize;
        }
        $comments = $DB->get_records_sql($sql, $params);

        $attachments = $this->get_attachments_of_comments($comments, 'attachments');

        // ICTODO: also get inline attachments
        foreach($comments as $comment) {
            self::comment_add_details($comment, $attachments);
        }

        if ($includedPostId !== 0) {
            $moreCommentsAfterTheIncluded = $this->get_posts_for_chat($includedPostId);
            $comments = array_merge($comments, $moreCommentsAfterTheIncluded);
        }
        return $comments;
    }

    /**
     * adds further details to a comment like author's name, email, username
     * and datetime which is a human readable version of the timecreated timestamp
     * @param $comment
     * @param $attachments
     * @return void
     * @throws \dml_exception
     */
    public static function comment_add_details(&$comment, $attachments) {
        global $DB;
        $context = \context_system::instance();
        $comment->datetime = userdate($comment->timecreated);
        $comment->author = $DB->get_record('user', ['id' => $comment->userid], 'id,firstname,lastname,email,username');
        $comment->author_fullname = self::get_author_fullname($comment->userid);
        $comment->comment = file_rewrite_pluginfile_urls($comment->comment, 'pluginfile.php', $context->id, 'local_learningcompanions', 'message', $comment->id);
        if (array_key_exists($comment->id, $attachments)) {
            $comment->attachments = $attachments[$comment->id];
        } else {
            $comment->attachments = [];
        }
        $comment->ratings = is_null($comment->ratings)?[]:explode(",", $comment->ratings);
        $comment->isratedbyuser = self::is_comment_rated_by_current_user($comment);
    }

    public static function get_author_fullname($userid) {
        global $DB;
        $user = $DB->get_record('user', ['id' => $userid]);
        if (!$user || $user->deleted == 1) {
            return get_string('deleted_user', 'local_learningcompanions');
        }
        return fullname($user);
    }

    /**
     * @param $comment
     * @return bool
     */
    public static function is_comment_rated_by_current_user($comment) {
        global $USER;
        if (empty($ratings) || !in_array($USER->id, $ratings)) {
            return false;
        }
        return true;
    }

    public function get_newest_posts(int $lastViewedPostId) {
        global $DB;

        //Get the newest comments
        $comments = $DB->get_records_sql('SELECT c.*, GROUP_CONCAT(r.userid) as ratings
                    FROM {lc_chat_comment} c
                        LEFT JOIN {lc_chat_comment_ratings} r ON r.commentid = c.id
                    WHERE c.chatid = ?
                      AND c.id > ?
                    GROUP BY c.id
                    ORDER BY timecreated DESC', [$this->chatid, $lastViewedPostId]);

        $this->set_latestviewedcomment($this->chatid);

        //Get the attachments
        $attachments = $this->get_attachments_of_comments($comments, 'attachments');

        foreach($comments as $comment) {
            self::comment_add_details($comment, $attachments);
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
        global $USER, $OUTPUT;
        $form = $this->get_submission_form(['chatid' => $this->chatid]);
        $languageStrings = $this->get_language_strings();
        $context = [
            'userid' => $USER->id,
            'groupid' => $this->groupid ?? 'undefined',
            'form' => $form,
            'languageStrings' => $languageStrings,
        ];
        return $OUTPUT->render_from_template('local_learningcompanions/chat', $context);
    }

    public function get_question_chat_module() {
        global $USER, $OUTPUT;
        $form = $this->get_submission_form(['chatid' => $this->chatid]);
        $languageStrings = $this->get_language_strings();

        $question = question::get_question_by_id($this->chat->relatedid);
        $context = [
            'userid' => $USER->id,
            'question' => $question,
            'form' => $form,
            'languageStrings' => $languageStrings,
        ];
        return $OUTPUT->render_from_template('local_learningcompanions/mentor/mentor_question_chat', $context);
    }

    protected function get_submission_form($customdata) {
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

    public function get_last_active_time() {
        global $DB;
        return $DB->get_field_sql('SELECT timecreated FROM {lc_chat_comment} WHERE chatid = ? ORDER BY timecreated DESC LIMIT 1', [$this->chatid]);
    }

    /**
     * @param bool $excludeCurrentUser True, if the current user should be ignored.
     *
     * @return false|int
     * @throws \dml_exception
     */
    public function get_last_active_userid(bool $excludeCurrentUser = false) {
        global $DB, $USER;

        $sql = 'SELECT cc.userid FROM mdl_lc_chat_comment cc
    LEFT JOIN mdl_lc_chat chat ON cc.chatid = chat.id
    LEFT JOIN mdl_lc_group_members members ON members.groupid = chat.relatedid AND chat.chattype = 1
         AND cc.userid = members.userid
    WHERE chatid = ? ';
        $params = [$this->chatid];

        if ($excludeCurrentUser) {
            $sql .= ' AND members.userid != ? ';
            $params[] = $USER->id;
        }

        $sql .= ' ORDER BY cc.timecreated DESC LIMIT 1;';

        return $DB->get_field_sql($sql, $params);
    }

}
