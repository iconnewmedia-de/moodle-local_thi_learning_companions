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

class chat {
    protected $chatid;
    protected $chat;
    protected $context;

    public static function create_group_chat($groupid): self {
        global $DB;

        if (!$DB->record_exists('thi_lc_groups', ['id' => $groupid])) {
            throw new \moodle_exception('groupnotfound', 'local_thi_learning_companions');
        }

        $newchat = new self();
        $newchat->groupid = $groupid;
        $newchat->chat = $DB->get_record('thi_lc_chat', ['relatedid' => $groupid, 'chattype' => groups::CHATTYPE_GROUP]);
        if ($groupid) {
            if (!$newchat->chat) {
                $chat = new \stdClass();
                $chat->chattype = 1;
                $chat->relatedid = $groupid;
                $chat->timecreated = time();
                $group = $DB->get_record('thi_lc_groups', ['id' => $groupid]);
                $chat->course = $group->courseid;
                $chatid = $DB->insert_record('thi_lc_chat', $chat);
                $newchat->chat = $DB->get_record('thi_lc_chat', ['id' => $chatid]);
            }
            $newchat->chatid = $newchat->chat->id;
        }
        $newchat->context = \context_system::instance();
        $newchat->filestorage = get_file_storage();
        return $newchat;
    }

    /**
     * gets called when users try to access the group chat of a closed group without being member
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function redirect_to_other_group_chat() {
        global $USER;
        $usergroups = groups::get_groups_of_user($USER->id);
        if (empty($usergroups)) {
            redirect('/local/thi_learning_companions/group/search.php',
                get_string('cant_chat_no_group_memberships', 'local_thi_learning_companions'),
                null,
            \core\output\notification::NOTIFY_INFO
            );
        } else {
            $firstgroup = current($usergroups);
            $groupid = $firstgroup->id;
            redirect('/local/thi_learning_companions/chat.php?groupid=' . $groupid);
        }
    }

    public static function create_question_chat($questionid): self {
        global $DB;

        $newchat = new self();
        $newchat->context = \context_system::instance();
        $newchat->filestorage = get_file_storage();
        $newchat->chat = $DB->get_record('thi_lc_chat', ['relatedid' => $questionid, 'chattype' => groups::CHATTYPE_MENTOR]);

        if (!$newchat->chat) {
            $chat = new \stdClass();
            $chat->chattype = groups::CHATTYPE_MENTOR;
            $chat->relatedid = $questionid;
            $chat->timecreated = time();
            $chat->course = 0;
            $chatid = $DB->insert_record('thi_lc_chat', $chat);
            $newchat->chat = $DB->get_record('thi_lc_chat', ['id' => $chatid]);
        }
        $newchat->chatid = $newchat->chat->id;

        return $newchat;
    }

    /**
     * @param $chatid
     * @return chat
     * @throws \dml_exception
     */
    public static function get_chat_by_id($chatid) {
        global $DB;
        $chat = new self();
        $chat->chat = $DB->get_record('thi_lc_chat', ['id' => $chatid], '*', MUST_EXIST);
        $chat->chatid = $chatid;
        return $chat;
    }

    private function get_language_strings(): string {
        $stringkeys = [
            'delete_post',
            'report_post',
            'no_posts_available',
            'not_allowed_to_see_posts',
        ];
        $strings = get_strings($stringkeys, 'local_thi_learning_companions');
        $stringsstring = json_encode($strings);
        return "M.str.local_thi_learning_companions = {...M.str.local_thi_learning_companions, ...$stringsstring};";
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
        // ICTODO: check if user has the permission to post to this chat.
        $obj = new \stdClass();
        $obj->chatid = $this->chatid;
        $obj->userid = $USER->id;
        $obj->comment = $comment; // ICTODO: sanitize this! prevent XSS and stuff!
        $obj->flagged = 0;
        $obj->totalscore = 0;
        $obj->timecreated = time();
        $obj->timemodified = 0;
        $DB->insert_record('thi_lc_chat_comment', $obj);
        // ICTODO: save attachments.
    }

    public function update_comment($comment, $attachments) {
        // ICTODO: update comment and attachments.
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
        $comments = $DB->get_records('thi_lc_chat_comment', ['chatid' => $this->chatid], 'timecreated DESC');
        $attachments = $this->get_attachments_of_comments($comments, 'attachments');
        $context = \context_system::instance();
        // ICTODO: also get inline attachments.
        foreach ($comments as $comment) {
            $comment->datetime = userdate($comment->timecreated);
            $comment->author = $DB->get_record('user', ['id' => $comment->userid], 'id,firstname,lastname,email,username');
            $comment->comment = file_rewrite_pluginfile_urls(
                $comment->comment,
                'pluginfile.php',
                $context->id,
                'local_thi_learning_companions',
                'message',
                $comment->id
            );
            if (array_key_exists($comment->id, $attachments)) {
                $comment->attachments = $attachments[$comment->id];
            } else {
                $comment->attachments = [];
            }
        }
        return $comments;
    }

    /**
     * @param int|null $firstpostid The last post id, that was already loaded. If null, the posts will be loaded from the beginning
     * @param int|null $includedpostid The id of the Post that should be included in the result even if it's older than $firstPostId
     *
     * @return array
     * @throws \dml_exception
     */
    public function get_posts_for_chat(int $firstpostid = null, int $includedpostid = 0) {
        global $DB;

        // If the user is not allowed to see the chat, return an empty array.
        if (!$this->can_view_chat()) {
            return [];
        }

        $stepsize = 5;

        if (is_null($firstpostid)) {
            $firstpostid = $DB->get_field_sql('SELECT MAX(id) FROM {thi_lc_chat_comment} WHERE chatid = ?', [$this->chatid]);
            $firstpostid++;
        }

        $sql = 'SELECT c.*, GROUP_CONCAT(r.userid) as ratings
                    FROM {thi_lc_chat_comment} c
                    LEFT JOIN {thi_lc_chat_comment_ratings} r ON r.commentid = c.id
                    WHERE c.chatid = :chatid AND c.id < :firstpostid ';
        $params = ['chatid' => $this->chatid, 'firstpostid' => $firstpostid];

        if ($includedpostid !== 0) {
            $sql .= ' AND c.id >= :includedpostid ';
            $params['includedpostid'] = $includedpostid;
        }

        $sql .= ' GROUP BY c.id ';
        $sql .= 'ORDER BY timecreated DESC ';
        if ($includedpostid === 0) {
            $sql .= ' LIMIT 0, '.$stepsize;
        }
        $comments = $DB->get_records_sql($sql, $params);

        $attachments = $this->get_attachments_of_comments($comments, 'attachments');

        // ICTODO: also get inline attachments.
        foreach ($comments as $comment) {
            self::comment_add_details($comment, $attachments);
        }

        if ($includedpostid !== 0) {
            $morecommentsaftertheincluded = $this->get_posts_for_chat($includedpostid);
            $comments = array_merge($comments, $morecommentsaftertheincluded);
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
        $comment->comment = file_rewrite_pluginfile_urls(
            $comment->comment,
            'pluginfile.php',
            $context->id,
            'local_thi_learning_companions',
            'message',
            $comment->id
        );
        if (array_key_exists($comment->id, $attachments)) {
            $comment->attachments = $attachments[$comment->id];
        } else {
            $comment->attachments = [];
        }
        $comment->ratings = is_null($comment->ratings) ? [] : explode(",", $comment->ratings);
        $comment->isratedbyuser = self::is_comment_rated_by_current_user($comment);
    }

    public static function get_author_fullname($userid) {
        global $DB;
        $user = $DB->get_record('user', ['id' => $userid]);
        if (!$user || $user->deleted == 1) {
            return get_string('deleted_user', 'local_thi_learning_companions');
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

    public function get_newest_posts(int $lastviewedpostid) {
        // If the user is not allowed to see the chat, return an empty array.
        if (!$this->can_view_chat()) {
            return [];
        }

        global $DB;

        // Get the newest comments.
        $comments = $DB->get_records_sql('SELECT c.*, GROUP_CONCAT(r.userid) as ratings
                    FROM {thi_lc_chat_comment} c
                        LEFT JOIN {thi_lc_chat_comment_ratings} r ON r.commentid = c.id
                    WHERE c.chatid = ?
                      AND c.id > ?
                    GROUP BY c.id
                    ORDER BY timecreated DESC', [$this->chatid, $lastviewedpostid]);

        $this->set_latestviewedcomment($this->chatid);

        // Get the attachments.
        $attachments = $this->get_attachments_of_comments($comments, 'attachments');

        foreach ($comments as $comment) {
            self::comment_add_details($comment, $attachments);
        }
        return $comments;
    }

    public function set_latestviewedcomment(int $chatid) {
        global $DB, $USER;
        $record = $DB->get_record('thi_lc_chat_lastvisited', ['chatid' => $chatid, 'userid' => $USER->id]);
        if ($record) {
            $record->timevisited = time();
            $DB->update_record('thi_lc_chat_lastvisited', $record);
            return;
        }
        $record = new \stdClass();
        $record->userid = $USER->id;
        $record->chatid = $chatid;
        $record->timevisited = time();
        $DB->insert_record('thi_lc_chat_lastvisited', $record);
    }

    public function get_attachments_of_comments(array $comments, string $area) {
        global $CFG;
        require_once($CFG->dirroot.'/local/thi_learning_companions/lib.php');
        return local_thi_learning_companions_get_attachments_of_chat_comments($comments, $area);
    }

    /**
     * returns the HTML and JS inclusions for the chat module to include on the chat page
     * @return mixed
     */
    public function get_chat_module() {
        global $USER, $OUTPUT;
        $form = $this->get_submission_form(['chatid' => $this->chatid]);
        $languagestrings = $this->get_language_strings();
        $context = [
            'userid' => $USER->id,
            'groupid' => $this->groupid ?? 'undefined',
            'form' => $form,
            'languageStrings' => $languagestrings,
        ];
        return $OUTPUT->render_from_template('local_thi_learning_companions/chat', $context);
    }

    public function get_question_chat_module() {
        global $USER, $OUTPUT;
        $form = $this->get_submission_form(['chatid' => $this->chatid]);
        $languagestrings = $this->get_language_strings();

        $question = question::get_question_by_id($this->chat->relatedid);
        $context = [
            'userid' => $USER->id,
            'question' => $question,
            'form' => $form,
            'languageStrings' => $languagestrings,
        ];
        return $OUTPUT->render_from_template('local_thi_learning_companions/mentor/mentor_question_chat', $context);
    }

    protected function get_submission_form($customdata) {
        require_once(__DIR__. "/chat_post_form.php");
        // ICTODO: Dynamically get the course and module from the currently selected group.

        $form = new \local_thi_learning_companions\chat_post_form(null, $customdata);
        $draftitemid = file_get_submitted_draft_itemid('attachments');
        $chatid = empty($customdata["chatid"]) ? null : $customdata["chatid"];
        $postid = empty($customdata["postid"]) ? null : $customdata["postid"];
        $attachoptions = \local_thi_learning_companions\chat_post_form::attachment_options();
        $context = \context_system::instance();
        file_prepare_draft_area($draftitemid, $context, 'local_thi_learning_companions', 'attachments', $postid, $attachoptions);
        $draftideditor = file_get_submitted_draft_itemid('message');

        $form->set_data(
            [
                'attachments' => $draftitemid,
                'subject' => '',
                'message' => [
                    'text' => '',
                    'format' => editors_get_preferred_format(),
                    'itemid' => $draftideditor,
                ],
                'chatid' => $chatid,
            ]
        );
        $output = $form->render();
        $output = str_replace(["col-md-3", "col-form-label d-flex", "col-md-9"], [
            "d-none", "col-form-label", "",
        ], $output);
        return $output;
    }

    public function get_last_active_time() {
        global $DB;
        return $DB->get_field_sql(
            'SELECT timecreated FROM {thi_lc_chat_comment} WHERE chatid = ? ORDER BY timecreated DESC LIMIT 1',
            [$this->chatid]
        );
    }

    /**
     * @param bool $excludecurrentuser True, if the current user should be ignored.
     *
     * @return false|int
     * @throws \dml_exception
     */
    public function get_last_active_userid(bool $excludecurrentuser = false) {
        global $DB, $USER;

        $sql = 'SELECT cc.userid FROM mdl_thi_lc_chat_comment cc
    LEFT JOIN mdl_thi_lc_chat chat ON cc.chatid = chat.id
    LEFT JOIN mdl_thi_lc_group_members members ON members.groupid = chat.relatedid AND chat.chattype = 1
         AND cc.userid = members.userid
    WHERE chatid = ? ';
        $params = [$this->chatid];

        if ($excludecurrentuser) {
            $sql .= ' AND members.userid != ? ';
            $params[] = $USER->id;
        }

        $sql .= ' ORDER BY cc.timecreated DESC LIMIT 1;';

        return $DB->get_field_sql($sql, $params);
    }

    private function can_view_chat(): bool {
        global $USER;

        // Admins can see all chats.
        if (has_capability('local/thi_learning_companions:group_manage', \context_system::instance())) {
            return true;
        }

        // If it´s a group chat, the user has to be a member, or the group must be public.
        if ((int)$this->chat->chattype === groups::CHATTYPE_GROUP) {
            $group = groups::get_group_by_id($this->chat->relatedid);
            if ($group->is_user_member($USER->id)) {
                return true;
            }
            if (!$group->closedgroup) {
                return true;
            }
            return false;
        }

        // If it´s a question chat, the user has to be a mentor, or the question creator.
        if ((int)$this->chat->chattype === groups::CHATTYPE_MENTOR) {
            return question::get_question_by_id($this->chat->relatedid)->can_user_view($USER->id);
        }

        return false;
    }

}
