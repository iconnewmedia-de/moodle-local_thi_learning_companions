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
        $collection->add_subsystem_link('local_thi_learning_companions', [], 'privacy:metadata:core_comment');
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

        $contextlist->add_system_context();

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
        $groupchatsubcontext = get_string('groupchatsubcontext', 'local_thi_learning_companions');
        $groupcreatedsubcontext = get_string('groupcreatedsubcontext', 'local_thi_learning_companions');
        $groupmembershipsubcontext = get_string('groupmembershipsubcontext', 'local_thi_learning_companions');
        $grouprequestsubcontext = get_string('grouprequestsubcontext', 'local_thi_learning_companions');
        $mentorsubcontext = get_string('mentorsubcontext', 'local_thi_learning_companions');
        $mentorquestionsubcontext = get_string('mentorquestionsubcontext', 'local_thi_learning_companions');
        foreach ($contexts as $context) {
            switch ($context->contextlevel) {
                case CONTEXT_MODULE:
                    $cmid = $context->instanceid;
                    self::export_chat_for_cmid($groupchatsubcontext, $context, $cmid, $userid);
                    self::export_groups_created_for_cmid_by_user($groupchatsubcontext, $context, $cmid, $userid);
                    self::export_group_memberships_for_cmid($groupchatsubcontext, $context, $cmid, $userid);
                    self::export_group_requests_for_cmid($groupchatsubcontext, $context, $cmid, $userid);
                    break;
                case CONTEXT_COURSE:
                    $courseid = $context->instanceid;
                    self::export_chat_for_course($groupchatsubcontext, $context, $courseid, $userid);
                    self::export_groups_created_for_course_by_user($groupchatsubcontext, $context, $courseid, $userid);
                    self::export_group_memberships_for_course($groupchatsubcontext, $context, $courseid, $userid);
                    self::export_group_requests_for_course($groupchatsubcontext, $context, $courseid, $userid);
                    break;
                case CONTEXT_SYSTEM:
                    self::export_chat($groupchatsubcontext, $context, $userid);
                    self::export_groups_created_by_user($groupcreatedsubcontext, $context, $userid);
                    self::export_group_memberships($groupmembershipsubcontext, $context, $userid);
                    self::export_group_requests($grouprequestsubcontext, $context, $userid);
                    self::export_mentorships($mentorsubcontext, $context, $userid);
                    self::export_mentor_questions($mentorquestionsubcontext, $context, $userid);
                    break;
                default:
                    // Nothing at the moment.
            }
        }
        // ICTODO: Implement export_user_data() method.
        /* Get data for the user with:
            thi_lc_groups.createdby
            thi_lc_group_members.userid
            thi_lc_group_requests.userid
            thi_lc_mentors.userid
            thi_lc_chat_comment.userid
            thi_lc_chat_comment_ratings.userid
            thi_lc_mentor_questions.askedby
            thi_lc_chat_lastvisited.userid
            thi_lc_tutor_notifications.tutorid
        */
    }

    /**
     * Exports chat that's related to a course module.
     * @param string $subcontext
     * @param \core\context $context
     * @param int $cmid
     * @param int $userid
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
            ->export_data([$subcontext], (object)$chatcomments);
    }

    /**
     * Exports groups that were created by the user for the course module
     * @param string $subcontext
     * @param \core\context $context
     * @param int $cmid
     * @param int $userid
     * @return void
     * @throws \dml_exception
     */
    protected static function export_groups_created_for_cmid_by_user($subcontext, $context, $cmid, $userid) {
        global $DB;
        $groups = $DB->get_records_sql("select g.*
            FROM {thi_lc_groups} g
            WHERE g.cmid = ? AND g.createdby = ?",
            [$cmid, $userid]
        );
        \core_privacy\local\request\writer::with_context($context)
            ->export_data([$subcontext], (object)$groups);
    }

    /**
     * Exports groups that were created by the user for the course module
     * @param string $subcontext
     * @param \core\context $context
     * @param int $courseid
     * @param int $userid
     * @return void
     * @throws \dml_exception
     */
    protected static function export_groups_created_for_course_by_user($subcontext, $context, $courseid, $userid) {
        global $DB;
        $groups = $DB->get_records_sql("select g.*
            FROM {thi_lc_groups} g
            WHERE g.courseid = ? AND g.createdby = ?",
            [$courseid, $userid]
        );
        \core_privacy\local\request\writer::with_context($context)
            ->export_data([$subcontext], (object)$groups);
    }

    /**
     * Exports groups that were created by the user with no course or course module context
     * @param string $subcontext
     * @param \core\context $context
     * @param int $userid
     * @return void
     * @throws \dml_exception
     */
    protected static function export_groups_created_by_user($subcontext, $context, $userid) {
        global $DB;
        $groups = $DB->get_records_sql("select g.*
            FROM {thi_lc_groups} g
            WHERE g.courseid = 0 AND g.cmid = 0 AND g.createdby = ?",
            [$userid]
        );
        \core_privacy\local\request\writer::with_context($context)
            ->export_data([$subcontext], (object)$groups);
    }

    /**
     * Exports the group memberships for groups that are related to a certain course module
     * @param string $subcontext
     * @param \core\context $context
     * @param int $cmid
     * @param int $userid
     * @return void
     * @throws \dml_exception
     */
    protected static function export_group_memberships_for_cmid($subcontext, $context, $cmid, $userid) {
        global $DB;
        $memberships = $DB->get_records_sql("select gm.*, g.name as groupname
            FROM {thi_lc_groups} g
            JOIN {thi_lc_group_members} gm ON gm.groupid = g.id
            WHERE g.cmid = ? AND gm.userid = ?",
            [$cmid, $userid]
        );
        \core_privacy\local\request\writer::with_context($context)
            ->export_data([$subcontext], (object)$memberships);
    }

    /**
     * Exports the group membership requests for groups that are related to a certain course module
     * @param string $subcontext
     * @param \core\context $context
     * @param int $cmid
     * @param int $userid
     * @return void
     * @throws \dml_exception
     */
    protected static function export_group_requests_for_cmid($subcontext, $context, $cmid, $userid) {
        global $DB;
        $requests = $DB->get_records_sql("select gr.*, g.name as groupname
            FROM {thi_lc_groups} g
            JOIN {thi_lc_group_requests} gr ON gr.groupid = g.id
            WHERE g.cmid = ? AND gr.userid = ?",
            [$cmid, $userid]
        );
        \core_privacy\local\request\writer::with_context($context)
            ->export_data([$subcontext], (object)$requests);
    }

    /**
     * Exports the group membership requests for groups that are related to a certain course
     * @param string $subcontext
     * @param \core\context $context
     * @param int $courseid
     * @param int $userid
     * @return void
     * @throws \dml_exception
     */
    protected static function export_group_requests_for_course($subcontext, $context, $courseid, $userid) {
        global $DB;
        $requests = $DB->get_records_sql("select gr.*, g.name as groupname
            FROM {thi_lc_groups} g
            JOIN {thi_lc_group_requests} gr ON gr.groupid = g.id
            WHERE g.courseid = ? AND gr.userid = ?",
            [$courseid, $userid]
        );
        \core_privacy\local\request\writer::with_context($context)
            ->export_data([$subcontext], (object)$requests);
    }

    /**
     * Exports the group membership requests for groups that are not related to a course or course module
     * @param string $subcontext
     * @param \core\context $context
     * @param int $userid
     * @return void
     * @throws \dml_exception
     */
    protected static function export_group_requests($subcontext, $context, $userid) {
        global $DB;
        $requests = $DB->get_records_sql("select gr.*, g.name as groupname
            FROM {thi_lc_groups} g
            JOIN {thi_lc_group_requests} gr ON gr.groupid = g.id
            WHERE g.courseid = 0 AND g.cmid = 0 AND gr.userid = ?",
            [$userid]
        );
        \core_privacy\local\request\writer::with_context($context)
            ->export_data([$subcontext], (object)$requests);
    }

    /**
     * Exports the information for which topic a user has become mentor.
     * @param string $subcontext
     * @param int $context
     * @param int $userid
     * @return void
     * @throws \dml_exception
     */
    protected static function export_mentorships($subcontext, $context, $userid) {
        global $DB;
        $mentorships = $DB->get_records('thi_lc_mentors', ['userid' => $userid]);
        \core_privacy\local\request\writer::with_context($context)
            ->export_data([$subcontext], (object)$mentorships);
    }

    /**
     * Exports all questions to mentors asked by the user
     * @param string $subcontext
     * @param \core\context $context
     * @param int $userid
     * @return void
     */
    protected static function export_mentor_questions($subcontext, $context, $userid) {
        global $DB;
        $questions = $DB->get_records('thi_lc_mentor_questions', ['askedby' => $userid]);
        \core_privacy\local\request\writer::with_context($context)
            ->export_data([$subcontext], (object)$questions);
    }

    /**
     * Exports the group memberships for groups that are related to a certain course module
     * @param string $subcontext
     * @param \core\context $context
     * @param int $courseid
     * @param int $userid
     * @return void
     * @throws \dml_exception
     */
    protected static function export_group_memberships_for_course($subcontext, $context, $courseid, $userid) {
        global $DB;
        $memberships = $DB->get_records_sql("select gm.*, g.name as groupname
            FROM {thi_lc_groups} g
            JOIN {thi_lc_group_members} gm ON gm.groupid = g.id
            WHERE g.courseid = ? AND gm.userid = ?",
            [$courseid, $userid]
        );
        \core_privacy\local\request\writer::with_context($context)
            ->export_data([$subcontext], (object)$memberships);
    }

    /**
     * Exports the group memberships for groups that are related to a certain course module
     * @param string $subcontext
     * @param \core\context $context
     * @param int $userid
     * @return void
     * @throws \dml_exception
     */
    protected static function export_group_memberships($subcontext, $context, $userid) {
        global $DB;
        $memberships = $DB->get_records_sql("select gm.*, g.name as groupname
            FROM {thi_lc_groups} g
            JOIN {thi_lc_group_members} gm ON gm.groupid = g.id
            WHERE g.courseid = 0 AND g.cmid = 0 AND gm.userid = ?",
            [$userid]
        );
        \core_privacy\local\request\writer::with_context($context)
            ->export_data([$subcontext], (object)$memberships);
    }

    /**
     * exports chat that's related to a course
     * @param string $subcontext
     * @param \core\context $context
     * @param int $courseid
     * @param int $userid
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
            ->export_data([$subcontext], (object)$chatcomments);
    }

    /**
     * exports chat that's not related to a course or course module
     * @param string $subcontext
     * @param \core\context $context
     * @param int $userid
     * @return void
     * @throws \dml_exception
     */
    protected static function export_chat($subcontext, $context, $userid) {
        global $DB;
        $chatcomments = $DB->get_records_sql('SELECT cmt.*
            FROM {thi_lc_chat_comment} cmt
            JOIN {thi_lc_chat} chat ON chat.id = cmt.chatid
            LEFT JOIN {thi_lc_groups} g ON g.id = chat.relatedid AND chat.chattype = ?
            WHERE ((g.courseid = 0 AND g.cmid = 0) OR chat.chattype = ?)
              AND cmt.userid = ?
            ',
            [groups::CHATTYPE_GROUP, groups::CHATTYPE_MENTOR, $userid]
        );
        \core_privacy\local\request\writer::with_context($context)
            ->export_data([$subcontext], (object)$chatcomments);
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
        if (empty($contextlist->count())) {
            return;
        }
        $userid = $contextlist->get_user()->id;
        $contexts = $contextlist->get_contexts();
        foreach ($contexts as $context) {
            if ($context instanceof \context_course) {
                $courseid = $context->instanceid;
                // Delete all data for the user for groups that are course related.
                self::delete_comments_for_user_and_course($userid, $courseid);
                self::delete_group_requests_for_user_and_course($userid, $courseid);
                self::delete_group_membership_for_user_and_course($userid, $courseid);
                self::delete_group_createdby_for_user_and_course($userid, $courseid);
            } else if ($context instanceof \context_module) {
                $cmid = $context->instanceid;
                // Delete all data for the user for groups that are course module related.
                self::delete_comments_for_user_and_cm($userid, $cmid);
                self::delete_group_requests_for_user_and_cm($userid, $cmid);
                self::delete_group_membership_for_user_and_cm($userid, $cmid);
                self::delete_group_createdby_for_user_and_cm($userid, $cmid);
            } else if ($context instanceof \context_system) {
                // Delete all data for the user for groups that are neither course- nor module related.
                self::delete_comments_for_user($userid);
                self::delete_tutor_notifications_for_user($userid);
                self::delete_mentor_questions_asked_by($userid);
                self::delete_mentorship_for_user($userid);
                self::delete_group_requests_for_user($userid);
                self::delete_group_membership_for_user($userid);
                self::delete_group_createdby_for_user($userid);
            }
        }
    }

    /**
     * deletes all comments from a user for the group chat of groups that are related to the given course id
     * doesn't delete the whole entry, just removes user-related info: userid, comment and sets the timedeleted stamp
     * @param int $userid
     * @param int $courseid
     * @return void
     */
    protected static function delete_comments_for_user_and_course($userid, $courseid) {
        global $DB;
        $DB->execute('UPDATE {thi_lc_groups} g
                    JOIN {thi_lc_chat} c ON c.relatedid = g.id AND c.chattype = ?
                    JOIN {thi_lc_chat_comment} cmt ON cmt.chatid = c.id AND cmt.userid = ?
                    SET cmt.timedeleted = ?, userid = 0, comment = \'\'
                    WHERE g.courseid = ?',
            [\local_thi_learning_companions\groups::CHATTYPE_GROUP, $userid, time(), $courseid]
        );
        // Delete last visited entry.
        $DB->execute('DELETE lvstd
                    FROM {thi_lc_groups} g
                    JOIN {thi_lc_chat} c ON c.relatedid = g.id AND c.chattype = ?
                    JOIN {thi_lc_chat_lastvisited} lvstd ON lvstd.chatid = c.id AND lvstd.userid = ?
                    WHERE g.courseid = ?',
            [\local_thi_learning_companions\groups::CHATTYPE_GROUP, $userid, $courseid]
        );
    }

    /**
     * deletes all comments from a user for the group chat of groups that are related to the given course module id
     * doesn't delete the whole entry, just removes user-related info: userid, comment and sets the timedeleted stamp
     * @param int $userid
     * @param int $cmid
     * @return void
     */
    protected static function delete_comments_for_user_and_cm($userid, $cmid) {
        global $DB;
        $DB->execute('UPDATE {thi_lc_groups} g
                    JOIN {thi_lc_chat} c ON c.relatedid = g.id AND c.chattype = ?
                    JOIN {thi_lc_chat_comment} cmt ON cmt.chatid = c.id AND cmt.userid = ?
                    SET cmt.timedeleted = ?, userid = 0, comment = \'\'
                    WHERE g.cmid = ?',
            [\local_thi_learning_companions\groups::CHATTYPE_GROUP, $userid, time(), $cmid]
        );
        // Delete last visited entry.
        $DB->execute('DELETE lvstd
                    FROM {thi_lc_groups} g
                    JOIN {thi_lc_chat} c ON c.relatedid = g.id AND c.chattype = ?
                    JOIN {thi_lc_chat_lastvisited} lvstd ON lvstd.chatid = c.id AND lvstd.userid = ?
                    WHERE g.cmid = ?',
            [\local_thi_learning_companions\groups::CHATTYPE_GROUP, $userid, $cmid]
        );
    }

    /**
     * deletes all comments from a user for the group chat of groups that are not related courses or modules
     * doesn't delete the whole entry, just removes user-related info: userid, comment and sets the timedeleted stamp
     * @param int $userid
     * @return void
     */
    protected static function delete_comments_for_user($userid) {
        global $DB;
        // Delete group chat comments for the user.
        $DB->execute('UPDATE {thi_lc_groups} g
                    JOIN {thi_lc_chat} c ON c.relatedid = g.id AND c.chattype = ?
                    JOIN {thi_lc_chat_comment} cmt ON cmt.chatid = c.id AND cmt.userid = ?
                    SET cmt.timedeleted = ?, userid = 0, comment = \'\'
                    WHERE g.cmid = 0 AND g.courseid = 0',
            [\local_thi_learning_companions\groups::CHATTYPE_GROUP, $userid, time()]
        );
        // Delete mentor chat comments for the user.
        $DB->execute('UPDATE {thi_lc_chat} c
                    JOIN {thi_lc_chat_comment} cmt ON cmt.chatid = c.id AND cmt.userid = ?
                    SET cmt.timedeleted = ?, userid = 0, comment = \'\'
                    WHERE c.chattype = ?',
            [$userid, time(), \local_thi_learning_companions\groups::CHATTYPE_MENTOR]
        );
        // Delete last visited entry for group chat.
        $DB->execute('DELETE lvstd
                    FROM {thi_lc_groups} g
                    JOIN {thi_lc_chat} c ON c.relatedid = g.id AND c.chattype = ?
                    JOIN {thi_lc_chat_lastvisited} lvstd ON lvstd.chatid = c.id AND lvstd.userid = ?
                    WHERE g.cmid = 0 AND g.courseid = 0',
            [\local_thi_learning_companions\groups::CHATTYPE_GROUP, $userid]
        );
        // Delete last visited entry for mentor chat.
        $DB->execute('DELETE lvstd
                    FROM {thi_lc_chat} c
                    JOIN {thi_lc_chat_lastvisited} lvstd ON lvstd.chatid = c.id AND lvstd.userid = ?
                    WHERE c.chattype = ?',
            [$userid, \local_thi_learning_companions\groups::CHATTYPE_MENTOR]
        );
        // Anonymize comment rating.
        $DB->execute('UPDATE {thi_lc_groups} g
                    JOIN {thi_lc_chat} c ON c.relatedid = g.id AND c.chattype = ?
                    JOIN {thi_lc_chat_comment} cmt ON cmt.chatid = c.id
                    JOIN {thi_lc_chat_comment_ratings} rtng ON rtng.commentid = cmt.id
                    SET rtng.userid = 0
                    WHERE g.cmid = 0 AND g.courseid = 0 AND rtng.userid = ?',
            [\local_thi_learning_companions\groups::CHATTYPE_GROUP, $userid]
        );
        $DB->execute('UPDATE {thi_lc_chat} c
                    JOIN {thi_lc_chat_comment} cmt ON cmt.chatid = c.id
                    JOIN {thi_lc_chat_comment_ratings} rtng ON rtng.commentid = cmt.id
                    SET rtng.userid = 0
                    WHERE c.chattype = ? AND rtng.userid = ?',
            [\local_thi_learning_companions\groups::CHATTYPE_MENTOR, $userid]
        );
    }

    /**
     * deletes notifications to tutors if the tutor is the user who requested data deletion
     * @param int $userid
     * @return void
     * @throws \dml_exception
     */
    protected static function delete_tutor_notifications_for_user($userid) {
        global $DB;
        $DB->delete_records('thi_lc_tutor_notifications', ['tutorid' => $userid]);
    }

    /**
     * deletes questions asked to mentors by the user
     * @param int $userid
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    protected static function delete_mentor_questions_asked_by($userid) {
        global $DB;
        $deleted = get_string('deleted', 'local_thi_learning_companions');
        $deletedquestion = get_string('deletedquestion', 'local_thi_learning_companions');
        $DB->execute(
            "UPDATE {thi_lc_mentor_questions}
                    SET askedby = 0, topic = ?, title = ?, question = ?
                    WHERE askedby = ?",
            [$deleted, $deleted, $deletedquestion, $userid]
        );
    }

    /**
     * deletes the entry that says that the user has become mentor for a certain topic
     * @param int $userid
     * @return void
     * @throws \dml_exception
     */
    protected static function delete_mentorship_for_user($userid) {
        global $DB;
        $DB->delete_records('thi_lc_mentors', ['userid' => $userid]);
    }

    /**
     * deletes group join requests for groups related to a certain course
     * @param int $userid
     * @param int $courseid
     * @return void
     * @throws \dml_exception
     */
    protected static function delete_group_requests_for_user_and_course($userid, $courseid) {
        global $DB;
        $DB->execute('DELETE req
                    FROM {thi_lc_groups} g
                    JOIN {thi_lc_group_requests} req ON req.gropuid = g.id
                    WHERE g.courseid = ? AND req.userid = ?',
            [$courseid, $userid]
        );
    }

    /**
     * deletes group join requests for groups related to a certain module
     * @param int $userid
     * @param int $cmid
     * @return void
     * @throws \dml_exception
     */
    protected static function delete_group_requests_for_user_and_cm($userid, $cmid) {
        global $DB;
        $DB->execute('DELETE req
                    FROM {thi_lc_groups} g
                    JOIN {thi_lc_group_requests} req ON req.gropuid = g.id
                    WHERE g.cmid = ? AND req.userid = ?',
            [$cmid, $userid]
        );
    }

    /**
     * deletes group join requests for the user
     * @param int $userid
     * @return void
     * @throws \dml_exception
     */
    protected static function delete_group_requests_for_user($userid) {
        global $DB;
        $DB->execute('DELETE req
                    FROM {thi_lc_groups} g
                    JOIN {thi_lc_group_requests} req ON req.gropuid = g.id
                    WHERE g.cmid = 0 AND g.courseid = 0 AND req.userid = ?',
            [$userid]
        );
    }

    /**
     * deletes the group membership for groups related to a certain course
     * @param int $userid
     * @param int $courseid
     * @return void
     * @throws \dml_exception
     */
    protected static function delete_group_membership_for_user_and_course($userid, $courseid) {
        global $DB;
        $DB->execute('DELETE mem
                    FROM {thi_lc_groups} g
                    JOIN {thi_lc_group_members} mem ON mem.gropuid = g.id
                    WHERE g.courseid = ? AND mem.userid = ?',
            [$courseid, $userid]
        );
    }

    /**
     * deletes the information who created the group for groups related to a certain course
     * @param int $userid
     * @param int $courseid
     * @return void
     * @throws \dml_exception
     */
    protected static function delete_group_createdby_for_user_and_course($userid, $courseid) {
        global $DB;
        $DB->execute('UPDATE {thi_lc_groups} g
                    SET g.createdby = 0
                    WHERE g.courseid = ? AND g.createdby = ?',
            [$courseid, $userid]
        );
    }

    /**
     * deletes the group membership for groups related to a certain module
     * @param int $userid
     * @param int $cmid
     * @return void
     * @throws \dml_exception
     */
    protected static function delete_group_membership_for_user_and_cm($userid, $cmid) {
        global $DB;
        $DB->execute('DELETE mem
                    FROM {thi_lc_groups} g
                    JOIN {thi_lc_group_members} mem ON mem.gropuid = g.id
                    WHERE g.cmid = ? AND mem.userid = ?',
            [$cmid, $userid]
        );
    }

    /**
     * deletes the information about who created the group for groups created by user for group related to module
     * @param int $userid
     * @param int $cmid
     * @return void
     * @throws \dml_exception
     */
    protected static function delete_group_createdby_for_user_and_cm($userid, $cmid) {
        global $DB;
        $DB->execute('UPDATE {thi_lc_groups} g
                    SET g.createdby = 0
                    WHERE g.cmid = ? AND g.createdby = ?',
            [$cmid, $userid]
        );
    }

    /**
     * deletes the group membership for groups that aren't tied to courses and/or modules
     * @param int $userid
     * @return void
     * @throws \dml_exception
     */
    protected static function delete_group_membership_for_user($userid) {
        global $DB;
        $DB->execute('DELETE mem
                    FROM {thi_lc_groups} g
                    JOIN {thi_lc_group_members} mem ON mem.gropuid = g.id
                    WHERE g.cmid = 0 AND g.courseid = 0 AND mem.userid = ?',
            [$userid]
        );
    }

    /**
     * deletes the information about who created the group if group was created by user who wants his/her data removed
     * @param int $userid
     * @return void
     * @throws \dml_exception
     */
    protected static function delete_group_createdby_for_user($userid) {
        global $DB;
        $DB->execute('UPDATE {thi_lc_groups} g
                    SET g.createdby = 0
                    WHERE g.cmid = 0 AND g.courseid = 0 AND g.createdby = ?',
            [$userid]
        );
    }
}
