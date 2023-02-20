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
 * @package     local_learningcompanions
 * @category    string
 * @copyright   2022 ICON Vernetzte Kommunikation GmbH <info@iconnewmedia.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Learning Companions';
$string['learningcompanions:group_create'] = 'Create groups';
$string['learningcompanions:group_manage'] = 'Manage groups';
$string['learningcompanions:delete_comments_of_others'] = 'Delete comments of other users';
$string['learningcompanions:mentor_view'] = 'View mentors';
$string['adminareaname'] = 'Learning Companions';
$string['datatables_url'] = $CFG->wwwroot . '/local/learningcompanions/lang/en/datatables.json';

$string['create_new_group'] = 'Create new group';
$string['no_permission_create_group'] = 'You don\'t have the permission to create groups';
$string['failed_create_group'] = 'Unexpected error. Creating a new group failed with the error message: ';
$string['category_for_groups'] = 'Category for Groups';
$string['configcategory'] = 'Please select the category which shall hold the courses for each group';
$string['learningcompanions_settings'] = 'Local plugin learningcompanions';
$string['group-me-up'] = 'Group me up';
$string['button_bg_color'] = 'Button background color';
$string['configbuttonbg'] = 'Background color for the "group my up" button. Use CSS color syntax';
$string['button_text_color'] = 'Button text color';
$string['configbuttoncolor'] = 'Text color for the "group my up" button. Use CSS color syntax';
$string['button_radius'] = 'Button radius';
$string['configbuttonradius'] = 'Button radius for the "group my up" button in pixels.';
$string['creategroup'] = 'Create group';
$string['groupname'] = 'Group name';
$string['maxlengthwarning'] = 'You may only use up to {$a} characters';
$string['groupdescription'] = 'Group description';
$string['closedgroup'] = 'Closed group';
$string['closedgroup_help'] = 'With a closed group, people have to request permission to join.
You will then have to decide for each request who may or may not join.
Discussions of closed groups are only visible to group members.
Open groups can be joined by anyone and the discussions are visible to the public.';
$string['keywords'] = 'Keywords';
$string['nokeywords'] = 'No keywords chosen yet';
$string['edit_group'] = 'Edit group';
$string['error_group_creation_failed'] = 'Error: Group creation failed with message: "{$a}"';
$string['error_group_edit_failed'] = 'Error: Editing the group failed with message: "{$a}"';
$string['button_css_selector'] = 'CSS selector for group me up button';
$string['configbuttoncssselector'] = 'Group me up buttons will automatically get placed on elements that match this CSS selector';
$string['group_topic'] = 'Topic';
$string['coursecontext'] = 'Course context';
$string['nuggetcontext'] = 'Learning nugget context';
$string['group_image'] = 'Group image';
$string['group_description'] = 'Group description';
$string['coursecontext_help'] = 'If this group\'s purpose relates to a course, please select the course here.
You can begin typing and autocomplete will suggest matching courses that you\'re enrolled in.';
$string['nuggetcontext_help'] = 'If this group\'s purpose relates to a certain learning nugget, you can select the nugget here.
You can begin typing and autocomplete will suggest matching nuggets that belong to the course you\'ve seleced above';
$string['keywords_help'] = 'Type into the search field and hit comma, enter or tab to add a keyword that describes the topic. You can add multiple keywords.';
$string['invite_member'] = 'Invite member';

// Mentor
$string['myquestions'] = 'My questions';
$string['mentorquestions'] = 'Mentor questions';
$string['mymentorquestions'] = 'My mentor questions';
$string['allmentorquestions'] = 'Mentor questions related to my topics';
$string['learningnuggetcomments'] = 'Latest learning nugget comments';
$string['title'] = 'Question title';
$string['topic'] = 'Topic';
$string['topics'] = 'Topics';
$string['name'] = 'Name';
$string['answers'] = 'Answers';
$string['badges'] = 'Badges';
$string['status'] = 'Status';
$string['actions'] = 'Actions';
$string['lastactivity'] = 'Last activity';
$string['questiondate'] = 'Question date';
$string['noquestionsfound'] = 'No questions found.';
$string['asknewquestion'] = 'Ask new question';
$string['groupimage_maxbytes'] = 'Group image max filesize ';
$string['configgroupimagemaxbytes'] = 'Limits the file size of image files that users upload for the group image';
$string['setting_commentactivities'] = 'Add comment block to activities';
$string['configcommentactivities'] = 'Comma-separated whitelist of activities that should automatically receive a comment block';
$string['group_created'] = 'Your group was created successfully';
$string['listgroups'] = 'Group list';
$string['learninggroups'] = 'Learning groups';
$string['loading'] = 'Loading';
$string['reply'] = 'Reply';
$string['attachment'] = 'Attachment';
$string['attachment_help'] = 'You can optionally attach one or more files to a forum post. If you attach an image, it will be displayed after the message.';
$string['message'] = 'Message';
$string['post'] = 'Post';
$string['send'] = 'Send';
$string['nolearningnuggetcommentsfound'] = 'No learning nugget comments found.';
$string['questionclosedon'] = 'Question closed on {$a}';
$string['deletemyquestion'] = 'Delete my question';
$string['modal-deletemyquestion-title'] = 'Delete question';
$string['modal-deletemyquestion-text'] = 'Do you want to delete your question ("{$a}") and all related answers?';
$string['modal-deletemyquestion-okaybutton'] = 'Delete question';
$string['modal-deletemyquestion-cancelbutton'] = 'Cancel';
$string['notification_d'] = 'Question deleted successfully.';
$string['notification_n_d'] = 'Could not delete question.';
$string['findmentor'] = 'Find a mentor';
$string['askquestiontomentor'] = 'Ask question';
$string['nomentorsfound'] = 'Could not find any mentors';
$string['issupermentor'] = 'Super mentor';

// Group
$string['findgroup'] = 'Find group';
$string['topic'] = 'Topic';
$string['name'] = 'Name';
$string['join'] = 'Joining';
$string['membercount'] = 'Members';
$string['course'] = 'Course';
$string['createdon'] = 'Created on';
$string['lastactivity'] = 'Last activity';
$string['nogroupsfound'] = 'Could not find any group.';
$string['newgroupbutton'] = 'Create new group';
$string['closedgroup'] = 'Closed group';
$string['opengroup'] = 'Open group';
$string['modal-groupdetails-groupname'] = 'Group: {$a}';
$string['modal-groupdetails-description'] = 'Group description';
$string['modal-groupdetails-reference'] = 'Reference';
$string['modal-groupdetails-administrator'] = 'Group administrator';
$string['modal-groupdetails-visitgroup'] = 'Go to group';
$string['modal-groupdetails-topic'] = 'Topic';
$string['modal-groupdetails-members'] = 'Members';
$string['modal-groupdetails-activity'] = 'Activity';
$string['modal-groupdetails-createdate'] = 'Created on';
$string['modal-groupdetails-join'] = 'Joining';
$string['gotogroupbutton'] = 'Go to group';
$string['leavegroup'] = 'Leave Group';
$string['invite_to_group'] = 'Invite';
$string['request_join_group'] = 'Request to join group';
$string['group_join_not_possible'] = 'Joining the group is not possible now.';
$string['group_request_not_possible'] = 'Request to joining the group is not possible now.';
$string['group_edit_not_allowed'] = 'You don\'t have permission to edit this group.';

// Navigation
$string['lcadministration_comments'] = 'Tagged comments';
$string['lcadministration_groups'] = 'Groups';
$string['lcadministration'] = 'Learning companions administration';

// This should not need any translation for other language packs. Please let 'en' be the first language.
$string['profile_field_status_default_options'] = '<span lang="en" class="multilang">Online</span><span lang="de" class="multilang">Online</span>
<span lang="en" class="multilang">Offline</span><span lang="de" class="multilang">Offline</span>
<span lang="en" class="multilang">Please do not disturb</span><span lang="de" class="multilang">Bitte nicht stören</span>';
$string['profile_field_status_default_default'] = '<span lang="en" class="multilang">Offline</span><span lang="de" class="multilang">Offline</span>';
$string['profile_field_category_status_default'] = '<span lang="en" class="multilang">Status</span><span lang="de" class="multilang">Status</span>';

//Filtering
$string['filter_all_status'] = 'All groups';
$string['filter_open_status'] = 'Open groups';
$string['filter_closed_status'] = 'Closed groups';
$string['filter_members_count'] = 'Min. number of members';
$string['filter_all_topics'] = 'All topics';
$string['filter_keywords_placeholder'] = 'Name/Keyword';
$string['filter_badges'] = 'Badge';
$string['filter_super_mentor'] = 'Super mentor';

require '_messages.php';
require '_navbar.php';
require '_chat.php';

$string['modal-deletecomment-title'] = 'Delete this comment?';
$string['modal-deletecomment-text'] = 'Are you sure that you want to delete your comment? This can\'t be undone!';
$string['modal-deletecomment-okaybutton'] = 'Delete comment';
$string['modal-cancelbutton'] = 'Cancel';

$string['modal-editcomment-title'] = 'Edit comment';
$string['modal-editcomment-okaybutton'] = 'Save changes';

$string['modal-reportcomment-title'] = 'Report comment';
$string['modal-reportcomment-text'] = 'Are you sure that you want to report this comment? Please only report comments that are against the general guidelines.';
$string['modal-reportcomment-cancelbutton'] = 'Cancel';
$string['modal-reportcomment-okaybutton'] = 'Report comment';

$string['notification_uf'] = 'Comment successfully unflagged';
$string['notification_n_uf'] = 'Could not unflagg comment';
$string['notification_d'] = 'Comment successfully deleted.';
$string['notification_n_d'] = 'Could not delete comment.';

$string['previewing_group'] = 'You can´t send messages to this group, because you are not a member.';
$string['join_group_link_text'] = 'Join group';
$string['group_invite_title'] = 'Invite User to group';
$string['no_open_requests'] = 'There are currently no open requests.';
$string['process_requests'] = 'Process requests';
$string['last_user_leaves_closed_group_description'] = 'You are trying to leave a closed group. If you leave the group, the group will be deleted. This can not be undone.';
$string['leave_group'] = 'Leave group';
$string['assign_new_admin_while_leaving_description'] = 'You are the last admin of this group. If you leave the group, you have to assign a new admin.';
$string['choose_new_admin'] = 'Choose a new admin. The default is the last active member.';
$string['user_is_not_group_admin'] = 'You are not allowed to do this. You are not the admin of this group.';
$string['new_admin_is_not_member'] = 'The new admin is not a member of this group. Please choose a new admin.';

$string['bigbluebutton_title'] = 'Video conference with BigBlueButton';
$string['bigbluebutton_join_text'] = 'Click here to join the BigBlueButton video conference';

$string['setting_badgetypes_for_mentors'] = 'Mentor badges';
$string['configbadgetypes_for_mentors'] = 'Which badges qualify for the mentor role? Comma-separated list of badge names.';
$string['customfield_topic_description'] = 'This course\'s topic that is relevant for learning companions, groups and mentorships';
$string['your_mentorship_qualifications'] = 'You\'ve qualified to become mentor for the following topic(s):';
$string['no_mentorship_qualifications'] = 'There currently aren\'t any topics for which you can become mentor.';
$string['your_mentorships'] = 'You\'re currently registered as a mentor for the following topic(s):';
$string['my_mentorships'] = 'My mentorships';
$string['become_mentor'] = 'Become mentor for this topic';
$string['mentorship_already_assigned'] = 'You\'re already assigned to the topic &bdquo;{$a}&rdquo;';
$string['mentorship_error_invalid_topic_assignment'] = 'Error: You\'re not qualified to become mentor for the topic &bdquo;{$a}&rdquo;';
$string['mentorship_assigned_to_topic'] = 'Success - You\'re now assigned as a mentor for the topic &bdquo;{$a}&rdquo;';
$string['mentorship_error_unknown'] = 'Error - A problem occurred. Error message: &bdquo;{$a}&rdquo;';
$string['mentor_question_topic'] = 'Question topic';
$string['mentor_question_subject'] = 'Subject';
$string['mentor_question_body'] = 'Question';
$string['mentor'] = 'Mentor';
$string['all_mentors'] = 'All mentors';
$string['ask_mentor'] = 'Ask a mentor';
$string['submit_question'] = 'Submit question';
$string['mentor_question_added'] = 'Your question has been submitted.';
$string['my_questions'] = 'My questions';