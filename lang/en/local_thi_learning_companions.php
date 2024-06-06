<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * Das Projekt THISuccessAI (FBM202-EA-1690-07540) wird im Rahmen der Förderlinie „Hochschulen durch Digitalisierung stärken“ durch die Stiftung Innovation in der Hochschulehre gefördert.
 *
 * @package     local_thi_learning_companions
 * @category    string
 * @copyright   2022 ICON Vernetzte Kommunikation GmbH <info@iconnewmedia.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['actions'] = 'Actions';
$string['adminareaname'] = 'Learning Companions';
$string['all_mentors'] = 'All mentors';
$string['allmentorquestions'] = 'Mentor questions related to my topics';
$string['answers'] = 'Answers';
$string['ask_mentor'] = 'Ask a mentor';
$string['ask_new_open_question'] = 'Ask new open question';
$string['ask_new_question'] = 'Ask new question';
$string['ask_open_question'] = 'Submit question to all mentors';
$string['ask_open_question_description'] = '<p>All mentors who\'re qualified for the selected topic will be able to reply to the question</p>';
$string['ask_question'] = 'Submit question';
$string['askquestiontomentor'] = 'Ask question';
$string['assign_new_admin_while_leaving_description'] = 'You are the last admin of this group. If you leave the group, you have to assign a new admin.';
$string['attachment'] = 'Attachment';
$string['attachment_chat_filesize_excdeeded'] = 'The attachment couldn\'t be saved. The upload limit of {$a} has been reached for this group.';
$string['attachment_help'] = 'You can optionally attach one or more files to a forum post. If you attach an image, it will be displayed after the message.';
$string['badges'] = 'Badges';
$string['become_mentor'] = 'Become mentor for this topic';
$string['bigbluebutton_join_text'] = 'Click here to join the BigBlueButton video conference';
$string['bigbluebutton_title'] = 'Video conference with BigBlueButton';
$string['button_bg_color'] = 'Button background color';
$string['button_css_selector'] = 'CSS selector for group me up button';
$string['button_radius'] = 'Button radius';
$string['button_text_color'] = 'Button text color';
$string['cant_chat_no_group_memberships'] = '<strong>Notice</strong>: You tried to join a chat for a group that either doesn\'t exist (anymore) or that is a closed group and that you haven\'t joined yet. In case of the latter: You can send a join request to the group administrator.';
$string['category_for_groups'] = 'Category for Groups';
$string['choose_new_admin'] = 'Choose a new admin. The default is the last active member.';
$string['close_question'] = 'Mark question as solved';
$string['closedgroup'] = 'Closed group';
$string['closedgroup'] = 'Closed group';
$string['closedgroup_help'] = 'With a closed group, people have to request permission to join.
You will then have to decide for each request who may or may not join.
Discussions of closed groups are only visible to group members.
Open groups can be joined by anyone and the discussions are visible to the public.';
$string['comment_from'] = 'Comment from';
$string['configbadgetypes_for_mentors'] = 'Which badges qualify for the mentor role? Comma-separated list of badge names.';
$string['configbuttonbg'] = 'Background color for the "group my up" button. Use CSS color syntax';
$string['configbuttoncolor'] = 'Text color for the "group my up" button. Use CSS color syntax';
$string['configbuttoncssselector'] = 'Group me up buttons will automatically get placed on elements that match this CSS selector';
$string['configbuttonradius'] = 'Button radius for the "group my up" button in pixels.';
$string['configcategory'] = 'Please select the category which shall hold the courses for each group';
$string['configcommentactivities'] = 'Comma-separated whitelist of activities that should automatically receive a comment block';
$string['configgroupimagemaxbytes'] = 'Limits the file size of image files that users upload for the group image';
$string['configinform_tutors_about_unanswered_questions_after_x_days'] = 'Sometimes when users ask a question to mentors, these might remain unanswered. In these cases a tutor shall get informed about the unanswered question, so that he/she can assist.';
$string['configlatestcomments_max_amount'] = 'Maximum amount of comments listed in "latest comments" for mentors';
$string['configsupermentor_minimum_ratings'] = 'How many positive comment ratings must a mentor receive to become supermentor?';
$string['configtutorrole_shortname'] = 'Please insert the shortname of the role that your system uses for tutors here.';
$string['configuploadlimit_per_chat'] = 'The total sum of files that get uploaded to one chat group may not exceed this amount of MB';
$string['configuploadlimit_per_message'] = 'Files that get uploaded to the chat may not exceed this amount of MB per message';
$string['confirm_requested_join'] = 'Handle group join requests';
$string['course'] = 'Course';
$string['coursecontext'] = 'Course context';
$string['coursecontext_help'] = 'If this group\'s purpose relates to a course, please select the course here.
You can begin typing and autocomplete will suggest matching courses that you\'re enrolled in.';
$string['create_new_group'] = 'Create new group';
$string['createdon'] = 'Created on';
$string['creategroup'] = 'Create group';
$string['crontask'] = 'Regular tasks for thi_learning_companions';
$string['customfield_topic_description'] = 'This course\'s topic that is relevant for learning companions, groups and mentorships';
$string['datatables_url'] = $CFG->wwwroot . '/local/thi_learning_companions/js/lang_datatables/en/datatables.json';
$string['date_from'] = 'from';
$string['delete_post'] = 'Delete post';
$string['deleted_user'] = 'Deleted user';
$string['deletemyquestion'] = 'Delete my question';
$string['edit_group'] = 'Edit group';
$string['error_group_creation_failed'] = 'Error: Group creation failed with message: "{$a}"';
$string['error_group_edit_failed'] = 'Error: Editing the group failed with message: "{$a}"';
$string['event_comment_created'] = 'Comment created';
$string['event_comment_reported'] = 'Comment reported';
$string['event_group_created'] = 'Group created';
$string['event_group_deleted'] = 'Group deleted';
$string['event_group_joined'] = 'Group joined';
$string['event_group_searched'] = 'Group searched';
$string['event_group_updated'] = 'Group updated';
$string['event_group_updated'] = 'Group updated';
$string['event_mentor_assigned'] = 'Mentor assigned';
$string['event_question_answered'] = 'Question answered';
$string['event_question_created'] = 'Question created';
$string['event_question_responded'] = 'Question responded';
$string['event_super_mentor_assigned'] = 'Super mentor assigned';
$string['failed_create_group'] = 'Unexpected error. Creating a new group failed with the error message: ';
$string['filter_all_keywords'] = 'All keywords';
$string['filter_all_status'] = 'All groups';
$string['filter_all_topics'] = 'All topics';
$string['filter_badges'] = 'Badge';
$string['filter_closed_status'] = 'Closed groups';
$string['filter_creation_date'] = 'Minimum creation date';
$string['filter_designation_placeholder'] = 'Group designation';
$string['filter_members_count'] = 'Count members';
$string['filter_mentor_keywords_placeholder'] = 'Name/Keywords';
$string['filter_open_status'] = 'Open groups';
$string['filter_super_mentor'] = 'Super mentor';
$string['findgroup'] = 'Find group';
$string['findmentor'] = 'Find a mentor';
$string['gotogroupbutton'] = 'Go to group';
$string['group-me-up'] = 'Group me up';
$string['group_closed'] = 'Closed group';
$string['group_created'] = 'Your group was created successfully';
$string['group_description'] = 'Group description';
$string['group_edit_not_allowed'] = 'You don\'t have permission to edit this group.';
$string['group_edited'] = 'Group edited successfully.';
$string['group_image'] = 'Group image';
$string['group_invite_noselection'] = 'No users selected yet';
$string['group_invite_placeholder'] = 'Please type to search for users';
$string['group_invite_title'] = 'Invite User to group';
$string['group_join_not_possible'] = 'Joining the group is not possible now.';
$string['group_request_error_code_1'] = 'A Request has already been sent.';
$string['group_request_error_code_2'] = 'You can not request to join, because you are already a member of this group.';
$string['group_request_error_code_3'] = 'Creating the request failed.';
$string['group_request_error_code_666'] = 'Request to joining the group is not possible now.';
$string['group_topic'] = 'Keyword(s)';
$string['groupchatsubcontext'] = 'THI Learning Companions group chat';
$string['groupdescription'] = 'Group description';
$string['groupimage_maxbytes'] = 'Group image max filesize ';
$string['groupjoin_request_group'] = 'Group: {$a}';
$string['groupjoin_request_user'] = 'User: {$a}';
$string['groupname'] = 'Group name';
$string['groupnotfound'] = 'Group not found.';
$string['invalid_question_id'] = 'Invalid question id';
$string['invite_member'] = 'Invite member';
$string['invite_to_group'] = 'Invite';
$string['inviteusers'] = 'Invite user(s)';
$string['issupermentor'] = 'Super mentor';
$string['join'] = 'Joining';
$string['join_group_link_text'] = 'Join group';
$string['keywords'] = 'Keywords';
$string['keywords_help'] = 'Type into the search field and hit comma, enter or tab to add a keyword that describes the topic. You can add multiple keywords.';
$string['last_user_leaves_closed_group_description'] = '<p>You are trying to leave a closed group. If you leave the group, the group will be deleted. This can not be undone.</p>';
$string['lastactivity'] = 'Last activity';
$string['lastactivity'] = 'Last activity';
$string['latest_comments'] = 'Latest comments';
$string['lcadministration'] = 'Learning companions administration';
$string['lcadministration_comments'] = 'Tagged comments';
$string['lcadministration_groups'] = 'Groups';
$string['learning_nugget'] = 'Learning nugget';
$string['learninggroups'] = 'Learning groups';
$string['learningnuggetcomments'] = 'Latest learning nugget comments';
$string['leave_group'] = 'Leave group';
$string['leavegroup'] = 'Leave Group';
$string['listgroups'] = 'Group list';
$string['loading'] = 'Loading';
$string['manage_mentorships'] = 'Manage mentorships';
$string['maxlengthwarning'] = 'You may only use up to {$a} characters';
$string['membercount'] = 'Members';
$string['mentor'] = 'Mentor';
$string['mentor_question_added'] = 'Your question has been submitted.';
$string['mentor_question_body'] = 'Question';
$string['mentor_question_subject'] = 'Subject';
$string['mentor_question_topic'] = 'Question topic';
$string['mentor_role'] = 'THI Learning Companions Mentor';
$string['mentor_role_description'] = 'Users who have qualified as mentor for certain topics by achieving badges within the courses for that topic.';
$string['mentorquestions'] = 'Mentor questions';
$string['mentorship_already_assigned'] = 'You\'re already assigned to the topic &bdquo;{$a}&rdquo;';
$string['mentorship_assigned_to_topic'] = 'Success - You\'re now assigned as a mentor for the topic &bdquo;{$a}&rdquo;';
$string['mentorship_error_invalid_topic_assignment'] = 'Error: You\'re not qualified to become mentor for the topic &bdquo;{$a}&rdquo;';
$string['mentorship_error_unknown'] = 'Error - A problem occurred. Error message: &bdquo;{$a}&rdquo;';
$string['message'] = 'Message';
$string['message_appointed_to_admin_body'] = 'Hello {$a->receivername},

{$a->sendername} has appointed you to admin of group "{$a->groupname}".';
$string['message_appointed_to_admin_body_html'] = '<h4>Hello {$a->receivername},</h4>
<p>
{$a->sendername} has appointed you to admin of group "{$a->groupname}".</p>';
$string['message_appointed_to_admin_small'] = 'You are now the admin of group {$a}';
$string['message_appointed_to_admin_subject'] = 'Appointed to admin';
$string['message_group_join_accepted_body'] = 'Hello {$a->receivername},

{$a->sendername} has accepted your request to join group "{$a->groupname}".';
$string['message_group_join_accepted_body_html'] = '<h4>Hello {$a->receivername},</h4>
<p>
{$a->sendername} has accepted your request to join group "{$a->groupname}".</p>';
$string['message_group_join_accepted_small'] = 'Your request to join group {$a} has been accepted';
$string['message_group_join_accepted_subject'] = 'Group Join request accepted';
$string['message_group_join_denied_body'] = 'Hello {$a->receivername},

{$a->sendername} has denied your request to join group "{$a->groupname}".';
$string['message_group_join_denied_body_html'] = '<h4>Hello {$a->receivername},</h4>
<p>
{$a->sendername} has denied your request to join group "{$a->groupname}".</p>';
$string['message_group_join_denied_small'] = 'Your request to join group {$a} has been denied';
$string['message_group_join_denied_subject'] = 'Group Join request denied';
$string['message_group_join_requested_body'] = 'Hello {$a->receivername},

{$a->sendername} has requested to join your group "{$a->groupname}".
Please visit the group page to accept or decline the request.';
$string['message_group_join_requested_body_html'] = '<h4>Hello {$a->receivername},</h4>
<p>
{$a->sendername} has requested to join your group "{$a->groupname}".<br>
Please visit the group page to accept or decline the request.
</p>';
$string['message_group_join_requested_small'] = 'New request to join group {$a}';
$string['message_group_join_requested_subject'] = 'Group Join request';
$string['message_invited_to_group_body'] = 'Hello {$a->receivername},

{$a->sendername} has invited you into the group "{$a->groupname}".';
$string['message_invited_to_group_body_html'] = '<h4>Hello {$a->receivername},</h4>
<p>
{$a->sendername} has invited you into the group "{$a->groupname}".</p>';
$string['message_invited_to_group_small'] = 'You were invited into group {$a}';
$string['message_invited_to_group_subject'] = 'Group Invite';
$string['message_qualified_mentor_body'] = 'Dear {$a->firstname},#
You have completed the course "{$a->coursefullname}" with a great score and have thereby qualified to become a mentor for that course.
Becoming a mentor means that fellow students can use this platform to contact you and ask questions regarding the topics that you\'ve qualified for.
Whether you become a mentor or not is up to you, it is optional and completely voluntary.
To become a mentor, please follow this link: <a href="{$a->link}">{$a->link}</a>';
$string['message_qualified_mentor_smallmessage'] = 'Want to become a mentor?';
$string['message_qualified_mentor_subject'] = 'You have qualified as a mentor for the course {$a->coursefullname}';
$string['message_unanswered_question_body'] = 'Dear {$a->user_firstname} {$a->user_lastname}

A question by  {$a->askedby_firstname} {$a->askedby_lastname} has remained unanswered since it was asked on {$a->dateasked}:

Topic: {$a->topic}
Subject: {$a->title}
Question: {$a->question}

____________________________

Could you please assist and help the user?

Kind regards
Your automatic \'{$a->sitename}\' system notifier';
$string['message_unanswered_question_smallmessage'] = 'Unanswered question pending';
$string['message_unanswered_question_subject'] = 'Unanswered question by {$a->askedby_firstname} {$a->askedby_lastname} from {$a->dateasked}';
$string['messageprovider:appointed_to_admin'] = 'Appointed to admin';
$string['messageprovider:group_join_accepted'] = 'Group Join accepted';
$string['messageprovider:group_join_denied'] = 'Group Join denied';
$string['messageprovider:group_join_requested'] = 'Group Join request';
$string['messageprovider:invited_to_group'] = 'Invited to group';
$string['modal-cancelbutton'] = 'Cancel';
$string['modal-deletecomment-okaybutton'] = 'Delete comment';
$string['modal-deletecomment-text'] = 'Are you sure that you want to delete your comment? This can\'t be undone!';
$string['modal-deletecomment-title'] = 'Delete this comment?';
$string['modal-deletemyquestion-cancelbutton'] = 'Cancel';
$string['modal-deletemyquestion-okaybutton'] = 'Delete question';
$string['modal-deletemyquestion-text'] = 'Do you want to delete your question ("{$a}") and all related answers?';
$string['modal-deletemyquestion-title'] = 'Delete question';
$string['modal-editcomment-okaybutton'] = 'Save changes';
$string['modal-editcomment-title'] = 'Edit comment';
$string['modal-groupdetails-activity'] = 'Activity';
$string['modal-groupdetails-administrator'] = 'Group administrator';
$string['modal-groupdetails-createdate'] = 'Created on';
$string['modal-groupdetails-description'] = 'Group description';
$string['modal-groupdetails-groupname'] = 'Group: {$a}';
$string['modal-groupdetails-join'] = 'Joining';
$string['modal-groupdetails-leavetitle'] = 'Leave group';
$string['modal-groupdetails-members'] = 'Members';
$string['modal-groupdetails-needsnewadmin'] = 'Please choose a new administrator for the group';
$string['modal-groupdetails-reference'] = 'Reference';
$string['modal-groupdetails-topic'] = 'Keyword(s)';
$string['modal-groupdetails-visitgroup'] = 'Go to group';
$string['modal-reportcomment-cancelbutton'] = 'Cancel';
$string['modal-reportcomment-okaybutton'] = 'Report comment';
$string['modal-reportcomment-text'] = 'Are you sure that you want to report this comment? Please only report comments that are against the general guidelines.';
$string['modal-reportcomment-title'] = 'Report comment';
$string['my_mentorships'] = 'My mentorships';
$string['my_questions'] = 'My questions';
$string['mymentorquestions'] = 'My mentor questions';
$string['myquestions'] = 'My questions';
$string['name'] = 'Name';
$string['name'] = 'Name';
$string['navbar_confirm_join'] = 'Confirm join';
$string['navbar_create_group'] = 'Create new group';
$string['navbar_edit_group'] = 'Edit group';
$string['navbar_findgroups'] = 'Find groups';
$string['navbar_findmentors'] = 'Find mentors';
$string['navbar_groups'] = 'Groups';
$string['navbar_mentorquestions'] = 'Mentor questions';
$string['navbar_mentors'] = 'Mentors';
$string['new_admin_is_not_member'] = 'The new admin is not a member of this group. Please choose a new admin.';
$string['newgroupbutton'] = 'Create new group';
$string['no_group_duplicates_allowed'] = 'You have already created a group with that name for the same course. Please avoid creating duplicate groups.';
$string['no_mentorship_qualifications'] = 'There currently aren\'t any topics for which you can become mentor.';
$string['no_open_requests'] = 'There are currently no open requests.';
$string['no_permission_create_group'] = 'You don\'t have the permission to create groups';
$string['no_permission_for_this_chat'] = 'You don\'t have permission to view this chat';
$string['no_permission_for_this_question'] = 'You don\'t have permission to view this question';
$string['no_permission_invite_group'] = 'You don\'t have the permission to invite users to this group';
$string['no_permission_to_create_course'] = 'You have no permission to create a group for that course - you need to be enrolled in the course first';
$string['no_permission_to_delete_question'] = 'You don\'t have permission to delete this question';
$string['no_posts_available'] = 'No posts available';
$string['nogroupsfound'] = 'Could not find any group.';
$string['nokeywords'] = 'No keywords chosen yet';
$string['nolearningnuggetcommentsfound'] = 'No learning nugget comments found.';
$string['nomentorsfound'] = 'Could not find any mentors';
$string['noquestionsfound'] = 'No questions found.';
$string['not_allowed_to_see_posts'] = 'You are not allowed to see posts in this group';
$string['not_question_owner'] = 'You are not the owner of this question. You are not allowed to close it.';
$string['notification_d'] = 'Comment successfully deleted.';
$string['notification_d'] = 'Question deleted successfully.';
$string['notification_n_d'] = 'Could not delete comment.';
$string['notification_n_d'] = 'Could not delete question.';
$string['notification_n_uf'] = 'Could not unflagg comment';
$string['notification_uf'] = 'Comment successfully unflagged';
$string['nousersfound'] = 'Could not find any users matching your search.';
$string['nuggetcontext'] = 'Learning nugget context';
$string['nuggetcontext_help'] = 'If this group\'s purpose relates to a certain learning nugget, you can select the nugget here.
You can begin typing and autocomplete will suggest matching nuggets that belong to the course you\'ve seleced above';
$string['open_questions_to_my_topics'] = 'Open questions to my topics';
$string['opengroup'] = 'Open group';
$string['please_choose'] = 'Please choose';
$string['please_choose_a_topic'] = 'Please choose a topic';
$string['pluginname'] = 'Learning Companions';
$string['post'] = 'Post';
$string['previewing_group'] = 'You can´t send messages to this group, because you are not a member.';
$string['privacy:metadata:thi_lc_chat_comment:chatid'] = 'Chat id';
$string['privacy:metadata:thi_lc_chat_comment:comment'] = 'Comment';
$string['privacy:metadata:thi_lc_chat_comment:flagged'] = 'Flagged';
$string['privacy:metadata:thi_lc_chat_comment:flaggedby'] = 'Flagged by';
$string['privacy:metadata:thi_lc_chat_comment:timecreated'] = 'Time created';
$string['privacy:metadata:thi_lc_chat_comment:timedeleted'] = 'Time deleted';
$string['privacy:metadata:thi_lc_chat_comment:timemodified'] = 'Time modified';
$string['privacy:metadata:thi_lc_chat_comment:userid'] = 'User id';
$string['privacy:metadata:thi_lc_chat_comment_ratings:commentid'] = 'Comment id';
$string['privacy:metadata:thi_lc_chat_comment_ratings:userid'] = 'User id';
$string['privacy:metadata:thi_lc_chat_lastvisited:chatid'] = 'Chat id';
$string['privacy:metadata:thi_lc_chat_lastvisited:timevisited'] = 'Time visited';
$string['privacy:metadata:thi_lc_chat_lastvisited:userid'] = 'User id';
$string['privacy:metadata:thi_lc_group_members:groupid'] = 'Group id';
$string['privacy:metadata:thi_lc_group_members:isadmin'] = 'is admin';
$string['privacy:metadata:thi_lc_group_members:joined'] = 'Joined';
$string['privacy:metadata:thi_lc_group_members:userid'] = 'User id';
$string['privacy:metadata:thi_lc_group_requests:denied'] = 'Denied';
$string['privacy:metadata:thi_lc_group_requests:groupid'] = 'Group id';
$string['privacy:metadata:thi_lc_group_requests:timecreated'] = 'time created';
$string['privacy:metadata:thi_lc_group_requests:userid'] = 'User id';
$string['privacy:metadata:thi_lc_groups:closedgroup'] = 'Closed group';
$string['privacy:metadata:thi_lc_groups:cmid'] = 'Course module id';
$string['privacy:metadata:thi_lc_groups:courseid'] = 'Course id';
$string['privacy:metadata:thi_lc_groups:createdby'] = 'Created by';
$string['privacy:metadata:thi_lc_groups:description'] = 'Description';
$string['privacy:metadata:thi_lc_groups:name'] = 'Name';
$string['privacy:metadata:thi_lc_groups:timecreated'] = 'time created';
$string['privacy:metadata:thi_lc_groups:timemodified'] = 'time modified';
$string['privacy:metadata:thi_lc_mentor_answers:answer'] = 'Answer';
$string['privacy:metadata:thi_lc_mentor_answers:issolution'] = 'Marked as solution';
$string['privacy:metadata:thi_lc_mentor_answers:questionid'] = 'Question id';
$string['privacy:metadata:thi_lc_mentor_answers:timecreated'] = 'Time created';
$string['privacy:metadata:thi_lc_mentor_answers:userid'] = 'User id';
$string['privacy:metadata:thi_lc_mentor_questions:askedby'] = 'Asked By';
$string['privacy:metadata:thi_lc_mentor_questions:mentorid'] = 'Mentor id';
$string['privacy:metadata:thi_lc_mentor_questions:question'] = 'Question';
$string['privacy:metadata:thi_lc_mentor_questions:timeclosed'] = 'Time closed';
$string['privacy:metadata:thi_lc_mentor_questions:timecreated'] = 'Time created';
$string['privacy:metadata:thi_lc_mentor_questions:title'] = 'Title';
$string['privacy:metadata:thi_lc_mentor_questions:topic'] = 'Topic';
$string['privacy:metadata:thi_lc_mentors:topic'] = 'Topic';
$string['privacy:metadata:thi_lc_mentors:userid'] = 'User id';
$string['privacy:metadata:thi_lc_tutor_notifications:questionid'] = 'Question id';
$string['privacy:metadata:thi_lc_tutor_notifications:timecreated'] = 'Time created';
$string['privacy:metadata:thi_lc_tutor_notifications:tutorid'] = 'Tutor id';
$string['privacy:metadata:thi_lc_users_mentors:mentorid'] = 'Mentor id';
$string['privacy:metadata:thi_lc_users_mentors:userid'] = 'User id';
$string['private_questions_to_me'] = 'Private questions to me';
$string['process_requests'] = 'Process requests';
$string['profile_field_category_status_default'] = '<span lang="en" class="multilang">Status</span><span lang="de" class="multilang">Status</span>';
$string['profile_field_status_default_default'] = '<span lang="en" class="multilang">Online</span><span lang="de" class="multilang">Online</span>';
$string['profile_field_status_default_options'] = '<span lang="en" class="multilang">Online</span><span lang="de" class="multilang">Online</span>
<span lang="en" class="multilang">Offline</span><span lang="de" class="multilang">Offline</span>
<span lang="en" class="multilang">Please do not disturb</span><span lang="de" class="multilang">Bitte nicht stören</span>';
$string['question'] = 'Question';
$string['question_asked'] = 'Your question has been added and mentors will now be able to answer.';
$string['question_closed'] = 'Question closed';
$string['questionclosedon'] = 'Question closed on {$a}';
$string['questiondate'] = 'Question date';
$string['questionnotfound'] = 'Question not found';
$string['reply'] = 'Reply';
$string['report_post'] = 'Report post';
$string['request_join_group'] = 'Request to join group';
$string['request_sent'] = 'The join request has been sent to the group administrator.';
$string['search_mentor'] = 'Search mentor';
$string['selectusers'] = 'Select user(s)';
$string['send'] = 'Send';
$string['setting_badgetypes_for_mentors'] = 'Mentor badges';
$string['setting_commentactivities'] = 'Add comment block to activities';
$string['setting_inform_tutors_about_unanswered_questions_after_x_days'] = 'Inform tutors about unanswered questions after x days';
$string['setting_latestcomments_max_amount'] = 'Latest comments: Max. amount';
$string['setting_supermentor_minimum_ratings'] = 'Minimum ratings to become supermentor';
$string['setting_tutorrole_shortname'] = 'Shortname of tutor role';
$string['setting_uploadlimit_per_chat'] = 'Upload limit per chat';
$string['setting_uploadlimit_per_message'] = 'Upload limit per message';
$string['status'] = 'Status';
$string['subject'] = 'Subject';
$string['subject_help'] = 'Please summarize the question here in a few words.';
$string['submit_question'] = 'Submit question';
$string['thi_learning_companions'] = 'Learning Companions';
$string['thi_learning_companions:delete_comments_of_others'] = 'Delete comments of other users';
$string['thi_learning_companions:group_create'] = 'Create groups';
$string['thi_learning_companions:group_manage'] = 'Manage groups';
$string['thi_learning_companions:mentor_view'] = 'View mentors';
$string['thi_learning_companions_settings'] = 'Local plugin thi_learning_companions';
$string['title'] = 'Question title';
$string['topic'] = 'Topic';
$string['topic'] = 'Topic';
$string['topic_help'] = 'The courses that you are enrolled in can have topics assigned to them. Here you can select from the topics of your courses.';
$string['topics'] = 'Topics';
$string['upload_title'] = 'Upload file';
$string['user_is_not_group_admin'] = 'You are not allowed to do this. You are not the admin of this group.';
$string['users_invited'] = 'The selected user(s) got invited';
$string['warning'] = 'Warning';
$string['your_mentorship_qualifications'] = 'You\'ve qualified to become mentor for the following topic(s):';
$string['your_mentorships'] = 'You\'re currently registered as a mentor for the following topic(s):';
$string['youve_become_supermentor_body'] = 'Congratulations, you\'ve earned {$a} positive ratings on your comments and have thereby become a supermentor. This means that you\'ll appear as a supermentor in the mentor search, thereby signifying that you\'re especially qualified to help.';
$string['youve_become_supermentor_short'] = 'Congratulations, your comments are popular. You\'re now a supermentor.';
$string['youve_become_supermentor_subject'] = 'You\'re now a supermentor';