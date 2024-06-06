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
namespace local_thi_learning_companions;

use local_thi_learning_companions\event\comment_created;
use local_thi_learning_companions\event\comment_reported;
use local_thi_learning_companions\event\question_responded;
use local_thi_learning_companions\event\super_mentor_assigned;

/**
 * Class with methods for handling chats
 */
class chats {
    /**
     *
     */
    const CHAT_TYPE_MENTOR = 0;
    /**
     *
     */
    const CHAT_TYPE_GROUP = 1;

    /**
     * Returns the chat for a given group id
     * @param int $groupid
     * @return false|mixed
     * @throws \dml_exception
     */
    public static function get_chat_of_group(int $groupid) {
        global $DB;
        $chatid = $DB->get_field(
            'thi_lc_chat',
            'id',
            ['relatedid' => $groupid, 'chattype' => self::CHAT_TYPE_GROUP]
        );
        return $chatid;
    }

    /**
     * Handles the posting of a chat comment
     * @param $comment
     * @param $formdata
     * @param $editoroptions
     * @return bool success status of saving file attachment (if any). false if limit of file sizes has been reached for this chat
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function post_comment($comment, $formdata, $editoroptions) {
        global $USER, $DB;

        // Check if the user is a member of the group.
        $chatid = $comment->chatid;
        $mayviewchat = self::may_view_chat($chatid);
        if (!$mayviewchat) {
            throw new \Exception(get_string('no_permission_for_this_chat', 'local_thi_learning_companions'));
        }

        $chat = chat::get_chat_by_id($chatid);

        $context    = \context_system::instance();
        $comment->timecreated = time();
        $comment->userid     = $USER->id;
        $comment->attachment = "";
        $comment->comment = "";
        $comment->id = $DB->insert_record("thi_lc_chat_comment", $comment);
        $draftitemid = file_get_submitted_draft_itemid('comment');
        $allowedtags = [
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
        $comment->message = strip_tags($comment->message, implode('', $allowedtags));
        $comment->comment = file_save_draft_area_files(
            $draftitemid,
            $context->id,
            'local_thi_learning_companions',
            'message',
            $comment->id,
            $editoroptions,
            $comment->message
        );
        $DB->set_field('thi_lc_chat_comment', 'comment', $comment->comment, ['id' => $comment->id]);
        $success = self::add_attachment($comment, $formdata);
        self::set_latest_comment($comment->chatid);

        if ((int)$chat->chattype === groups::CHATTYPE_MENTOR) {
            question_responded::make($USER->id, $chatid, $comment->id)->trigger();
        } else {
            comment_created::make($USER->id, $comment->chatid, $comment->id)->trigger();
        }

        return $success;
    }

    /**
     * Returns true if the current user may view the chat with the given chat id
     * @param $chatid
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function may_view_chat($chatid) {
        global $DB;
        $chat = $DB->get_record('thi_lc_chat', ['id' => $chatid], '*', MUST_EXIST);
        if ($chat->chattype == \local_thi_learning_companions\groups::CHATTYPE_GROUP) {
            return \local_thi_learning_companions\groups::may_view_group($chat->relatedid);
        }
        return self::may_view_mentorchat($chat->relatedid);
    }

    /**
     * Returns true if the user may view the chat for a certain question to mentors
     * @param $questionid
     * @return bool
     * @throws \dml_exception
     */
    public static function may_view_mentorchat($questionid) {
        global $DB, $USER;
        $question = $DB->get_record('thi_lc_mentor_questions', ['id' => $questionid], '*', MUST_EXIST);
        if ($USER->id === $question->mentorid || $USER->id === $question->askedby) {
            return true;
        }
        if (!empty($question->mentorid)) {
            return false;
        }
        return \local_thi_learning_companions\mentors::is_mentor_for_topic($USER->id, $question->topic);
    }

    /**
     * stores date of latest comment in group
     * @param $chatid
     * @return void
     * @throws \dml_exception
     */
    protected static function set_latest_comment($chatid) {
        global $DB;
        $chat = $DB->get_record('thi_lc_chat', ['id' => $chatid]);
        if (!$chat) {
            return;
        }
        if ($chat->chattype == self::CHAT_TYPE_GROUP) {
            $group = $DB->get_record('thi_lc_groups', ['id' => $chat->relatedid]);
            if (!$group) {
                return;
            }
            $group->latestcomment = time();
            $DB->update_record('thi_lc_groups', $group);
        }
    }

    /**
     * Adds an attachment
     * @param $comment
     * @param $formdata
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    protected static function add_attachment($comment, $formdata) {
        // Add the attachment.
        $context = \context_system::instance();
        // Make sure that the user doesn't exceed the chat's total limit for file uploads.
        $chatmaxuploadexceeded = self::check_uploadsize_total_exceeded($comment, $formdata);
        if ($chatmaxuploadexceeded) {
            return false; // ICTODO return an error message instead.
        }

        file_save_draft_area_files(
            $comment->attachments,
            $context->id,
            'local_thi_learning_companions',
            'attachments',
            $comment->id,
            \local_thi_learning_companions\chat_post_form::attachment_options()
        );
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
        require_once($CFG->dirroot . '/lib/setuplib.php');
        $config = get_config('local_thi_learning_companions');
        $maxbytes = intval($config->upload_limit_per_chat) . "M";
        $maxbytes = get_real_size($maxbytes);
        $draftinfo = file_get_draft_area_info($comment->attachments);
        $areasize = $draftinfo['filesize_without_references'];

        if ($areasize === 0) {
            return false;
        }
        $chat = \local_thi_learning_companions\chat::get_chat_by_id($comment->chatid);
        $totalsize = $areasize;
        $comments = $chat->get_comments();
        $attachments = $chat->get_attachments_of_comments($comments, 'attachments');
        foreach ($attachments as $attachment) {
            if (!is_array($attachment)) {
                continue;
            }
            foreach ($attachment as $file) {
                $totalsize += $file['filesize'];
            }
        }
        if ($totalsize > $maxbytes) {
            return true;
        }
        return false;
    }

    /**
     * Handles the flagging of a comment.
     * @param $commentid
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function flag_comment($commentid) {
        global $DB, $USER;
        $comment = $DB->get_record('thi_lc_chat_comment', ['id' => $commentid]);
        // ICTODO: make sure the user has the permission to flag this comment,
        // e.g. the user has to be in the group or have admin rights otherwise.
        $comment->flagged = 1;
        $comment->flaggedby = $USER->id;
        $comment->timemodified = time();
        $result = $DB->update_record('thi_lc_chat_comment', $comment);

        comment_reported::make($commentid, $USER->id)->trigger();

        return $result;
    }

    /**
     * Unflags a comment.
     * @param $commentid
     * @return bool
     * @throws \dml_exception
     */
    public static function unflag_comment($commentid) {
        global $DB;
        if ($comment = $DB->get_record('thi_lc_chat_comment', ['id' => $commentid])) {
            $comment->flagged = 0;
            $comment->timemodified = time();
            return $DB->update_record('thi_lc_chat_comment', $comment);
        }
        return false;
        // ICTODO: when comment gets unflagged, should field 'flaggedby' go NULL?
    }

    /**
     * Deletes a comment
     * @param $commentid
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function delete_comment($commentid) {
        global $DB, $USER;

        $comment = $DB->get_record('thi_lc_chat_comment', ['id' => $commentid]);
        if (!$comment) {
            return false;
        }

        $context = \context_system::instance();
        if ($comment->userid !== $USER->id &&
            !has_capability('local/thi_learning_companions:delete_comments_of_others', $context)) {
            return false;
        }

        $comment->timedeleted = time();
        return $DB->update_record('thi_lc_chat_comment', $comment);
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
        $rating = ['commentid' => $commentid, 'userid' => $USER->id];
        if ($DB->record_exists('thi_lc_chat_comment_ratings', $rating)) {
            $DB->delete_records('thi_lc_chat_comment_ratings', $rating);
            return false;
        }
        $DB->insert_record('thi_lc_chat_comment_ratings', $rating);
        $commentauthor = $DB->get_field('thi_lc_chat_comment', 'userid', ['id' => $commentid]);
        self::check_supermentor_qualification($commentauthor);
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
        $config = get_config('local_thi_learning_companions');
        $mincomments = intval($config->supermentor_minimum_ratings);
        $ratingcount = \local_thi_learning_companions\mentors::count_mentor_ratings($userid);
        if ($ratingcount === $mincomments) {
            \local_thi_learning_companions\messages::notify_supermentor($userid);
            super_mentor_assigned::make($userid)->trigger();
        }
    }

    /**
     * Returns all comments that have been flagged
     * @param bool $extended
     * @param bool $cut
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function get_all_flagged_comments(bool $extended = false, bool $cut = false): array {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/local/thi_learning_companions/lib.php');

        if ($comments = $DB->get_records('thi_lc_chat_comment', ['flagged' => 1], 'timecreated')) {
            $attachments = local_thi_learning_companions_get_attachments_of_chat_comments($comments, 'attachments');

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

    /**
     * Adds additional info to a comment that's not directly stored in the comment table
     * Such as the author's fullname
     * @param array $comments
     * @param bool $cut
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function add_extended_fields_to_comments(array $comments, bool $cut = false): array {
        global $CFG, $DB;

        foreach ($comments as $comment) {
            $comment->author = $DB->get_record('user', ['id' => $comment->userid]);
            $comment->author_fullname = chat::get_author_fullname($comment->author->id);
            $comment->author_profileurl = $CFG->wwwroot.'/user/profile.php?id='.$comment->userid;
            $comment->flaggedbyuser = $DB->get_record('user', ['id' => $comment->flaggedby]);
            $comment->flaggedbyuser_fullname = fullname($comment->flaggedbyuser);
            $comment->flaggedbyuser_profileurl = $CFG->wwwroot.'/user/profile.php?id='.$comment->flaggedby;
            $comment->commentdate = date('d.m.Y', $comment->timecreated);
            $comment->relatedchat_url = $CFG->wwwroot;
            $comment->groupId = $DB->get_field('thi_lc_chat', 'relatedid', ['id' => $comment->chatid]);

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
}
