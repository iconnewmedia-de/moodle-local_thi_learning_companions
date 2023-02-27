<?php
namespace local_learningcompanions;

class chats {
    const CHAT_TYPE_MENTOR = 0;
    const CHAT_TYPE_GROUP = 1;
    public static function get_chat_of_group(int $groupid) {
        global $DB;
        $chatid = $DB->get_field('lc_chat', 'id', array('relatedid' => $groupid, 'chattype' => self::CHAT_TYPE_GROUP));
        return $chatid;
    }

    public static function get_chat_of_activity(int $cmid) {

    }

    public static function get_all_chats_of_user() {

    }

    public static function get_all_chats_of_course() {

    }

    /**
     * @param $comment
     * @param $formdata
     * @param $editoroptions
     * @return bool success status of saving the file attachment (if any). returns false if the maximum limit of file sizes has been reached for this chat
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function post_comment($comment, $formdata, $editoroptions) {
        global $USER, $DB;

        //Check if the user is a member of the group
        $chatId = $comment->chatid;
        $mayViewChat = self::may_view_chat($chatId);
        if (!$mayViewChat) {
            throw new \Exception(get_string('no_permission_for_this_chat', 'local_learningcompanions'));
        }

        $context    = \context_system::instance();
        $comment->timecreated = time();
        $comment->userid     = $USER->id;
        $comment->attachment = "";
        $comment->comment = "";
        $comment->id = $DB->insert_record("lc_chat_comment", $comment);
        $draftitemid = file_get_submitted_draft_itemid('comment');
        $allowedTags = [
            '<div>',
            '<p>',
            '<a>',
            '<span>',
            '<strong>',
            '<em>',
            '<i>',
            '<b>',
            '<br>',
            '<del>',
            '<h1>',
            '<h2>',
            '<h3>',
            '<h4>',
            '<h5>',
            '<h6>',
            '<video>',
            '<audio>',
            '<img>',
            '<address>',
            '<ul>',
            '<ol>',
            '<li>',
            '<sub>',
            '<sup>',
            '<table>',
            '<thead>',
            '<tbody>',
            '<tfoot>',
            '<th>',
            '<tr>',
            '<td>',
        ];
        $comment->message = strip_tags($comment->message, implode('', $allowedTags));
        $comment->comment = file_save_draft_area_files($draftitemid, $context->id, 'local_learningcompanions', 'message', $comment->id,
            $editoroptions, $comment->message);
        $DB->set_field('lc_chat_comment', 'comment', $comment->comment, ['id'=>$comment->id]);
        $success = self::add_attachment($comment, $formdata);
        self::set_latest_comment($comment->chatid);

        return $success;
    }

    /**
     * @param $chatId
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function may_view_chat($chatId) {
        global $DB;
        $chat = $DB->get_record('lc_chat', ['id' => $chatId], '*', MUST_EXIST);
        if ($chat->chattype == \local_learningcompanions\groups::CHATTYPE_GROUP) {
            return \local_learningcompanions\groups::may_view_group($chat->relatedid);
        }
        return self::may_view_mentorchat($chat->relatedid);
    }

    /**
     * @param $questionId
     * @return bool
     * @throws \dml_exception
     */
    public static function may_view_mentorchat($questionId) {
        global $DB, $USER;
        $question = $DB->get_record('lc_mentor_questions', ['id' => $questionId], '*', MUST_EXIST);
        if ($USER->id === $question->mentorid || $USER->id === $question->askedby) {
            return true;
        }
        if (!empty($question->mentorid)) {
            return false;
        }
        return \local_learningcompanions\mentors::is_mentor_for_topic($USER->id, $question->topic);
    }

    /**
     * stores date of latest comment in group
     * @param $chatid
     * @return void
     * @throws \dml_exception
     */
    protected static function set_latest_comment($chatid) {
        global $DB;
        $chat = $DB->get_record('lc_chat', array('id' => $chatid));
        if (!$chat) {
            return;
        }
        if ($chat->chattype == self::CHAT_TYPE_GROUP) {
            $group = $DB->get_record('lc_groups', array('id' => $chat->relatedid));
            if (!$group) {
                return;
            }
            $group->latestcomment = time();
            $DB->update_record('lc_groups', $group);
        }
    }

    protected static function add_attachment($comment, $formdata) {
        // add the attachment
        $context = \context_system::instance();
        // ICTODO: make sure that the user doesn't exceed the chat's total limit for file uploads
        $chatMaxUploadExceeded = self::check_uploadsize_total_exceeded($comment, $formdata);
        if ($chatMaxUploadExceeded) {
            return false;
        }
        file_save_draft_area_files($comment->attachments, $context->id, 'local_learningcompanions', 'attachments', $comment->id,
            \local_learningcompanions\chat_post_form::attachment_options());
        return true;
    }

    /**
     * checks if the maximum of bytes for uploaded attachments gets exceeded with the new comment
     * @param $comment
     * @return bool
     * @throws \dml_exception
     */
    protected static function check_uploadsize_total_exceeded($comment) {
        global $CFG;
        require_once $CFG->dirroot . '/lib/setuplib.php';
        $config = get_config('local_learningcompanions');
        $maxbytes = intval($config->upload_limit_per_chat) . "M";
        $maxbytes = get_real_size($maxbytes);
        $draftinfo = file_get_draft_area_info($comment->attachments);
        $areasize = $draftinfo['filesize_without_references'];

//        if ($includereferences) {
//            $areasize = $draftinfo['filesize'];
//        }
        if ($areasize === 0) {
            return false;
        }
        $chat = \local_learningcompanions\chat::get_chat_by_id($comment->chatid);
        $totalsize = $areasize;
        $comments = $chat->get_comments();
        $attachments = $chat->get_attachments_of_comments($comments, 'attachments');
        foreach($attachments as $attachment) {
            if (!is_array($attachment)) {
                continue;
            }
            foreach($attachment as $file) {
                $totalsize += $file['filesize'];
            }
        }
        if ($totalsize > $maxbytes) {
            return true;
        }
        return false;
    }

    public static function report_comment($commentid) {

    }

    public static function flag_comment($commentid) {
        global $DB, $USER;
        $comment = $DB->get_record('lc_chat_comment', array('id' => $commentid));
        // ICTODO: make sure the user has the permission to flag this comment, e.g. the user has to be in the group or have admin rights otherwise
        $comment->flagged = 1;
        $comment->flaggedby = $USER->id;
        $comment->timemodified = time();
        return $DB->update_record('lc_chat_comment', $comment);
    }

    public static function unflag_comment($commentid) {
        global $DB;
        if ($comment = $DB->get_record('lc_chat_comment', array('id' => $commentid))) {
            $comment->flagged = 0;
            $comment->timemodified = time();
            return $DB->update_record('lc_chat_comment', $comment);
        }
        return false;
        // ICTODO: when comment gets unflagged, should field 'flaggedby' go NULL?
    }

    /**
     * @param $commentid
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function delete_comment($commentid) {
        global $DB, $USER;

        $comment = $DB->get_record('lc_chat_comment', ['id' => $commentid]);
        if (!$comment) {
            return false;
        }

        $context = \context_system::instance();
        if ($comment->userid !== $USER->id && !has_capability('local/learningcompanions:delete_comments_of_others', $context)) {
            return false;
        }

        $comment->timedeleted = time();
        return $DB->update_record('lc_chat_comment', $comment);
    }

    /**
     * toggles a user's rating for a comment
     * if the comment was already rated by the user, remove the rating
     * otherwise add a new rating
     * @param int $commentid
     * @return bool true if the comment is rated by the current user after calling this function, otherwise false
     * @throws \dml_exception
     */
    public static function rate_comment(int $commentid) {
        global $USER, $DB;
        $rating = array('commentid' => $commentid, 'userid' => $USER->id);
        if ($DB->record_exists('lc_chat_comment_ratings', $rating)) {
            $DB->delete_records('lc_chat_comment_ratings', $rating);
            return false;
        }
        $DB->insert_record('lc_chat_comment_ratings', $rating);
        $comment_author = $DB->get_field('lc_chat_comment', 'userid', array('id' => $commentid));
        self::check_supermentor_qualification($comment_author);
        return true;
    }

    /**
     * checks if a user has just reached the ammount of comments needed to become a supermentor
     * @param $userid
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    protected static function check_supermentor_qualification($userid) {
        global $DB;
        $config = get_config('local_learningcompanions');
        $minComments = intval($config->supermentor_minimum_ratings);
        $ratingCount = \local_learningcompanions\mentors::count_mentor_ratings($userid);
        if ($ratingCount === $minComments) {
            \local_learningcompanions\messages::notify_supermentor($userid);
        }
    }



    public static function get_all_flagged_comments(bool $extended = false, bool $cut = false): array {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/local/learningcompanions/lib.php');

        if ($comments = $DB->get_records('lc_chat_comment', ['flagged' => 1], 'timecreated')) {
            $attachments = local_learningcompanions_get_attachments_of_chat_comments($comments, 'attachments');

            foreach ($comments as $comment) {
                if (array_key_exists($comment->id, $attachments)) {
                    $comment->attachments = $attachments;
                } else {
                    $comment->attachments = [];
                }
                $comment->plaintext = strip_tags($comment->comment);
            }
            if ($extended) {
                return self::add_extended_fields_to_comments($comments, $cut);
            }
        }
        return $comments;
    }

    public static function add_extended_fields_to_comments(array $comments, bool $cut = false): array {
        global $CFG, $DB;

        foreach($comments as $comment) {
            $comment->author = $DB->get_record('user', ['id' => $comment->userid]);
            $comment->author_fullname = chat::get_author_fullname($comment->author->id);
            $comment->author_profileurl = $CFG->wwwroot.'/user/profile.php?id='.$comment->userid;
            $comment->flaggedbyuser = $DB->get_record('user', ['id' => $comment->flaggedby]);
            $comment->flaggedbyuser_fullname = fullname($comment->flaggedbyuser);
            $comment->flaggedbyuser_profileurl = $CFG->wwwroot.'/user/profile.php?id='.$comment->flaggedby;
            $comment->commentdate = date('d.m.Y', $comment->timecreated);
            $comment->relatedchat_url = $CFG->wwwroot;
            $comment->groupId = $DB->get_field('lc_chat', 'relatedid', ['id' => $comment->chatid]);

            $comment->origincomment = $comment->comment;
            if ($cut && strlen($comment->comment) > 100) {
                $comment->comment = substr($comment->comment, 0, 100).'...';
                $comment->commentcut = true;
            } else {
                $comment->commentcut = false;
            }
        }

        return $comments;
    }

    protected static function may_post_comment($chatid) {

    }


}
