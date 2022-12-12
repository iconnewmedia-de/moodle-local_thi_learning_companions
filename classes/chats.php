<?php
namespace local_learningcompanions;

class chats {
    const CHAT_TYPE_MENTOR = 0;
    const CHAT_TYPE_GROUP = 1;
    public static function get_chat_of_group(int $groupid) {

    }

    public static function get_chat_of_activity(int $cmid) {

    }

    public static function get_all_chats_of_user() {

    }

    public static function get_all_chats_of_course() {

    }

    public static function post_comment($comment, $formdata, $editoroptions) {
        global $USER, $DB;

        $context    = \context_system::instance();
        $comment->timecreated = time();
        $comment->userid     = $USER->id;
        $comment->attachment = "";
        $comment->comment = "";
        $comment->id = $DB->insert_record("lc_chat_comment", $comment);
        $draftitemid = file_get_submitted_draft_itemid('comment');
        $allowedTags = array(
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
        );
        $comment->message = strip_tags($comment->message, implode('', $allowedTags));
        $comment->comment = file_save_draft_area_files($draftitemid, $context->id, 'local_learningcompanions', 'message', $comment->id,
            $editoroptions, $comment->message);
        $DB->set_field('lc_chat_comment', 'comment', $comment->comment, array('id'=>$comment->id));
        self::add_attachment($comment, $formdata);
        self::set_latest_comment($comment->chatid);

        return $comment->id;
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
        file_save_draft_area_files($comment->attachments, $context->id, 'local_learningcompanions', 'attachments', $comment->id,
            \local_learningcompanions\chat_post_form::attachment_options());
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
        $comment = $DB->get_record('lc_chat_comment', array('id' => $commentid));
        if (!$comment) {
            return false;
        }
        $context = \context_system::instance();
        if ($comment->userid !== $USER->id && !has_capability('local/learningcompanions:delete_comments_of_others', $context)) {
            return false;
        }
        return $DB->delete_records('lc_chat_comment', array('id' => $commentid));
    }

    public static function get_all_flagged_comments(bool $extended = false, bool $cut = false): array {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/local/learningcompanions/lib.php');

        if ($comments = $DB->get_records('lc_chat_comment', array('flagged' => 1), 'timecreated')) {
            $attachments = local_learningcompanions_get_attachments_of_chat_comments($comments, 'attachments');

            foreach ($comments as $comment) {
                if (array_key_exists($comment->id, $attachments)) {
                    $comment->attachments = $attachments;
                } else {
                    $comment->attachments = [];
                }
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
            $comment->author = $DB->get_record('user', array('id' => $comment->userid));
            $comment->author_fullname = fullname($comment->author);
            $comment->author_profileurl = $CFG->wwwroot.'/user/profile.php?id='.$comment->userid;
            $comment->flaggedbyuser = $DB->get_record('user', array('id' => $comment->flaggedby));
            $comment->flaggedbyuser_fullname = fullname($comment->flaggedbyuser);
            $comment->flaggedbyuser_profileurl = $CFG->wwwroot.'/user/profile.php?id='.$comment->flaggedby;
            $comment->commentdate = date('d.m.Y', $comment->timecreated);
            $comment->relatedchat_url = $CFG->wwwroot;

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