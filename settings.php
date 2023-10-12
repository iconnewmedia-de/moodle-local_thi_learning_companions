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
 * Plugin administration pages are defined here.
 *
 * Das Projekt THISuccessAI (FBM202-EA-1690-07540) wird im Rahmen der Förderlinie „Hochschulen durch Digitalisierung stärken“ durch die Stiftung Innovation in der Hochschulehre gefördert.
 *
 * @package     local_learningcompanions
 * @category    admin
 * @copyright   2022 ICON Vernetzte Kommunikation GmbH <info@iconnewmedia.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    global $DB;
    $settings = new admin_settingpage( 'local_learningcompanions', get_string('learningcompanions_settings', 'local_learningcompanions') );
    $settings->add(new admin_setting_configtext('local_learningcompanions/groupimage_maxbytes', get_string('groupimage_maxbytes', 'local_learningcompanions'),
        get_string('configgroupimagemaxbytes', 'local_learningcompanions'), 1000000, PARAM_INT));

    $settings->add(new admin_setting_configtext('local_learningcompanions/commentactivities',
        get_string('setting_commentactivities', 'local_learningcompanions'),
        get_string('configcommentactivities', 'local_learningcompanions'),
    'assign,assignment,book,choice,data,feedback,folder,glossary,h5pactivity,lesson,lti,quiz,resource,page,scorm,survey,workshop'
    ));

    $settings->add(new admin_setting_configtext('local_learningcompanions/badgetypes_for_mentors',
        get_string('setting_badgetypes_for_mentors', 'local_learningcompanions'),
        get_string('configbadgetypes_for_mentors', 'local_learningcompanions'),
    'expert'
    ));

    $settings->add(new admin_setting_configtext('local_learningcompanions/supermentor_minimum_ratings',
        get_string('setting_supermentor_minimum_ratings', 'local_learningcompanions'),
        get_string('configsupermentor_minimum_ratings', 'local_learningcompanions'),
        10
    ));

    $settings->add(new admin_setting_configtext('local_learningcompanions/latest_comments_max_amount',
        get_string('setting_latestcomments_max_amount', 'local_learningcompanions'),
        get_string('configlatestcomments_max_amount', 'local_learningcompanions'),
        20
    ));

    $settings->add(new admin_setting_configtext('local_learningcompanions/upload_limit_per_message',
        get_string('setting_uploadlimit_per_message', 'local_learningcompanions'),
        get_string('configuploadlimit_per_message', 'local_learningcompanions'),
        5
    ));

    $settings->add(new admin_setting_configtext('local_learningcompanions/upload_limit_per_chat',
        get_string('setting_uploadlimit_per_chat', 'local_learningcompanions'),
        get_string('configuploadlimit_per_chat', 'local_learningcompanions'),
        100
    ));

    // inform_tutors_about_unanswered_questions_after_x_days
    $settings->add(new admin_setting_configtext('local_learningcompanions/inform_tutors_about_unanswered_questions_after_x_days',
        get_string('setting_inform_tutors_about_unanswered_questions_after_x_days', 'local_learningcompanions'),
        get_string('configinform_tutors_about_unanswered_questions_after_x_days', 'local_learningcompanions'),
        14
    ));

    // inform_tutors_about_unanswered_questions_after_x_days
    $settings->add(new admin_setting_configtext('local_learningcompanions/tutorrole_shortname',
        get_string('setting_tutorrole_shortname', 'local_learningcompanions'),
        get_string('configtutorrole_shortname', 'local_learningcompanions'),
        'teacher'
    ));

    $category = new admin_category('lcconfig', get_string('adminareaname', 'local_learningcompanions'));
    if (!$ADMIN->locate('lcconfig')) { // avoids "duplicate admin page name" warnings
        $ADMIN->add('root', $category);
    }
    $ADMIN->add('lcconfig', $settings);
}
