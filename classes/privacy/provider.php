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

namespace local_thi_learning_companions\privacy;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\context;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\writer;
use local_thi_learning_companions\groups;

/**
 * Privacy provider for local_thi_learning_companions
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider {
    /**
     * Returns all metadata for this privacy provider
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'thi_lc_groups',
            [
                'createdby' => 'privacy:metadata:thi_lc_groups:createdby',
                'name' => 'privacy:metadata:thi_lc_groups:name',
                'description' => 'privacy:metadata:thi_lc_groups:description',
                'closedgroup' => 'privacy:metadata:thi_lc_groups:closedgroup',
                'timecreated' => 'privacy:metadata:thi_lc_groups:timecreated',
                'timemodified' => 'privacy:metadata:thi_lc_groups:timemodified',
                'courseid' => 'privacy:metadata:thi_lc_groups:courseid',
                'cmid' => 'privacy:metadata:thi_lc_groups:cmid',
            ]
        );
        $collection->add_database_table(
            'thi_lc_group_members',
            [
                'userid' => 'privacy:metadata:thi_lc_group_members:userid',
                'groupid' => 'privacy:metadata:thi_lc_group_members:groupid',
                'isadmin' => 'privacy:metadata:thi_lc_group_members:isadmin',
                'joined' => 'privacy:metadata:thi_lc_group_members:joined',
            ]
        );
        $collection->add_database_table(
            'thi_lc_group_requests',
            [
                'userid' => 'privacy:metadata:thi_lc_group_requests:userid',
                'groupid' => 'privacy:metadata:thi_lc_group_requests:groupid',
                'timecreated' => 'privacy:metadata:thi_lc_group_requests:timecreated',
                'denied' => 'privacy:metadata:thi_lc_group_requests:denied',
            ]
        );
        $collection->add_database_table(
            'thi_lc_mentors',
            [
                'userid' => 'privacy:metadata:thi_lc_mentors:userid',
                'topic' => 'privacy:metadata:thi_lc_mentors:topic',
            ]
        );
        $collection->add_database_table(
            'thi_lc_users_mentors',
            [
                'userid' => 'privacy:metadata:thi_lc_users_mentors:userid',
                'mentorid' => 'privacy:metadata:thi_lc_users_mentors:mentorid',
            ]
        );
        $collection->add_database_table(
            'thi_lc_chat_comment',
            [
                'userid' => 'privacy:metadata:thi_lc_chat_comment:userid',
                'chatid' => 'privacy:metadata:thi_lc_chat_comment:chatid',
                'comment' => 'privacy:metadata:thi_lc_chat_comment:comment',
                'flagged' => 'privacy:metadata:thi_lc_chat_comment:flagged',
                'flaggedby' => 'privacy:metadata:thi_lc_chat_comment:flaggedby',
                'timecreated' => 'privacy:metadata:thi_lc_chat_comment:timecreated',
                'timedeleted' => 'privacy:metadata:thi_lc_chat_comment:timedeleted',
                'timemodified' => 'privacy:metadata:thi_lc_chat_comment:timemodified',
            ]
        );
        $collection->add_database_table(
            'thi_lc_chat_comment_ratings',
            [
                'userid' => 'privacy:metadata:thi_lc_chat_comment_ratings:userid',
                'commentid' => 'privacy:metadata:thi_lc_chat_comment_ratings:commentid',
            ]
        );
        $collection->add_database_table(
            'thi_lc_mentor_questions',
            [
                'askedby' => 'privacy:metadata:thi_lc_mentor_questions:askedby',
                'mentorid' => 'privacy:metadata:thi_lc_mentor_questions:mentorid',
                'topic' => 'privacy:metadata:thi_lc_mentor_questions:topic',
                'title' => 'privacy:metadata:thi_lc_mentor_questions:title',
                'question' => 'privacy:metadata:thi_lc_mentor_questions:question',
                'timeclosed' => 'privacy:metadata:thi_lc_mentor_questions:timeclosed',
                'timecreated' => 'privacy:metadata:thi_lc_mentor_questions:timecreated',
            ]
        );
        $collection->add_database_table(
            'thi_lc_mentor_answers',
            [
                'questionid' => 'privacy:metadata:thi_lc_mentor_answers:questionid',
                'userid' => 'privacy:metadata:thi_lc_mentor_answers:userid',
                'answer' => 'privacy:metadata:thi_lc_mentor_answers:answer',
                'issolution' => 'privacy:metadata:thi_lc_mentor_answers:issolution',
                'timecreated' => 'privacy:metadata:thi_lc_mentor_answers:timecreated',
            ]
        );
        $collection->add_database_table(
            'thi_lc_chat_lastvisited',
            [
                'userid' => 'privacy:metadata:thi_lc_chat_lastvisited:userid',
                'chatid' => 'privacy:metadata:thi_lc_chat_lastvisited:chatid',
                'timevisited' => 'privacy:metadata:thi_lc_chat_lastvisited:timevisited',
            ]
        );
        $collection->add_database_table(
            'thi_lc_tutor_notifications',
            [
                'questionid' => 'privacy:metadata:thi_lc_tutor_notifications:questionid',
                'tutorid' => 'privacy:metadata:thi_lc_tutor_notifications:tutorid',
                'timecreated' => 'privacy:metadata:thi_lc_tutor_notifications:timecreated',
            ]
        );
        return $collection;
    }

    /**
     * returns the contexts for a user
     * @param int $userid
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        // Group memberships from course module related groups.
        $sql = "SELECT c.id
                  FROM {context} c
                  JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = ?
                  JOIN {thi_lc_groups} g ON g.cmid = cm.id
                  LEFT JOIN {thi_lc_group_members} gm ON gm.groupid = g.id
                 WHERE g.createdby = ?
                    OR gm.userid = ?";
        $contextlist->add_from_sql($sql, [CONTEXT_MODULE, $userid, $userid]);

        // Group memberships from course related groups.
        $sql = "SELECT c.id
                  FROM {context} c
                  JOIN {course} crs ON crs.id = c.instanceid AND c.contextlevel = ?
                  JOIN {thi_lc_groups} g ON g.courseid = crs.id
                  LEFT JOIN {thi_lc_group_members} gm ON gm.groupid = g.id
                 WHERE g.createdby = ?
                    OR gm.userid = ?";
        $contextlist->add_from_sql($sql, [CONTEXT_COURSE, $userid, $userid]);
        return $contextlist;
    }

    /**
     * exports user data
     * @param approved_contextlist $contextlist
     * @return void
     * @throws \coding_exception
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        $userid = $contextlist->get_user()->id;
        $contexts = $contextlist->get_contexts();
        $contextids = $contextlist->get_contextids();
        $user = $contextlist->get_user();
        $groupchatsubcontext = get_string('groupchatsubcontext', 'local_thi_learning_companions');
        foreach ($contexts as $context) {
            switch ($context->contextlevel) {
                case CONTEXT_MODULE:
                    $cmid = $context->instanceid;
                    self::export_chat_for_cmid($groupchatsubcontext, $context, $cmid, $userid);
                    break;
                case CONTEXT_COURSE:
                    $courseid = $context->instanceid;
                    self::export_chat_for_course($groupchatsubcontext, $context, $courseid, $userid);
                    break;
                default:
                    // Nothing at the moment.
            }
        }
        // ICTODO: Implement export_user_data() method.
    }

    /**
     * Exports chat that's related to a course module.
     * @param $subcontext
     * @param $context
     * @param $cmid
     * @param $userid
     * @return void
     * @throws \dml_exception
     */
    protected static function export_chat_for_cmid($subcontext, $context, $cmid, $userid) {
        global $DB;
        $chatcomments = $DB->get_records_sql('SELECT cmt.*
            FROM {thi_lc_chat_comment} cmt
            JOIN {thi_lc_chat} chat ON chat.id = cmt.chatid
            JOIN {thi_lc_groups} g ON g.id = chat.relatedid AND chat.chattype = ?
            WHERE g.cmid = ?
              AND cmt.userid = ?
            ',
            [groups::CHATTYPE_GROUP, $cmid, $userid]
        );
        \core_privacy\local\request\writer::with_context($context)
            ->export_data([$subcontext], (object) [
                'comments' => $chatcomments,
            ]);
    }

    /**
     * exports chat that's related to a course
     * @param $subcontext
     * @param $context
     * @param $courseid
     * @param $userid
     * @return void
     * @throws \dml_exception
     */
    protected static function export_chat_for_course($subcontext, $context, $courseid, $userid) {
        global $DB;
        $chatcomments = $DB->get_records_sql('SELECT cmt.*
            FROM {thi_lc_chat_comment} cmt
            JOIN {thi_lc_chat} chat ON chat.id = cmt.chatid
            JOIN {thi_lc_groups} g ON g.id = chat.relatedid AND chat.chattype = ?
            WHERE g.courseid = ?
              AND cmt.userid = ?
            ',
            [groups::CHATTYPE_GROUP, $courseid, $userid]
        );
        \core_privacy\local\request\writer::with_context($context)
            ->export_data([$subcontext], (object) [
                'comments' => $chatcomments,
            ]);
    }


    /**
     * deletes all data for a user in the given context
     * @param \context $context
     * @return void
     * @throws \coding_exception
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        $coursecontext = $context->get_course_context();
        // ICTODO: Implement delete_data_for_all_users_in_context() method.
    }

    /**
     * deletes all data for a user
     * @param approved_contextlist $contextlist
     * @return void
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        $contexts = $contextlist->get_contexts();
        // ICTODO: Implement delete_data_for_user() method.
    }
}
