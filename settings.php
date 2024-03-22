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
 * Das Projekt THISuccessAI (FBM202-EA-1690-07540) wird im Rahmen der Förderlinie „Hochschulen durch Digitalisierung stärken“
 * durch die Stiftung Innovation in der Hochschulehre gefördert.
 *
 * @package     local_thi_learning_companions
 * @category    admin
 * @copyright   2022 ICON Vernetzte Kommunikation GmbH <info@iconnewmedia.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    global $DB;
    $settings = new admin_settingpage(
        'local_thi_learning_companions',
        get_string('thi_learning_companions_settings', 'local_thi_learning_companions')
    );
    $settings->add(
        new admin_setting_configtext('local_thi_learning_companions/groupimage_maxbytes',
            get_string('groupimage_maxbytes', 'local_thi_learning_companions'),
            get_string('configgroupimagemaxbytes', 'local_thi_learning_companions'),
            1000000,
            PARAM_INT
        )
    );

    $settings->add(new admin_setting_configtext('local_thi_learning_companions/commentactivities',
        get_string('setting_commentactivities', 'local_thi_learning_companions'),
        get_string('configcommentactivities', 'local_thi_learning_companions'),
    'assign,assignment,book,choice,data,feedback,folder,glossary,h5pactivity,lesson,lti,quiz,resource,page,scorm,survey,workshop'
    ));

    $settings->add(new admin_setting_configtext('local_thi_learning_companions/badgetypes_for_mentors',
        get_string('setting_badgetypes_for_mentors', 'local_thi_learning_companions'),
        get_string('configbadgetypes_for_mentors', 'local_thi_learning_companions'),
    'expert'
    ));

    $settings->add(new admin_setting_configtext('local_thi_learning_companions/supermentor_minimum_ratings',
        get_string('setting_supermentor_minimum_ratings', 'local_thi_learning_companions'),
        get_string('configsupermentor_minimum_ratings', 'local_thi_learning_companions'),
        10
    ));

    $settings->add(new admin_setting_configtext('local_thi_learning_companions/latest_comments_max_amount',
        get_string('setting_latestcomments_max_amount', 'local_thi_learning_companions'),
        get_string('configlatestcomments_max_amount', 'local_thi_learning_companions'),
        20
    ));

    $settings->add(new admin_setting_configtext('local_thi_learning_companions/upload_limit_per_message',
        get_string('setting_uploadlimit_per_message', 'local_thi_learning_companions'),
        get_string('configuploadlimit_per_message', 'local_thi_learning_companions'),
        5
    ));

    $settings->add(new admin_setting_configtext('local_thi_learning_companions/upload_limit_per_chat',
        get_string('setting_uploadlimit_per_chat', 'local_thi_learning_companions'),
        get_string('configuploadlimit_per_chat', 'local_thi_learning_companions'),
        100
    ));

    // Inform_tutors_about_unanswered_questions_after_x_days.
    $settings->add(
        new admin_setting_configtext(
            'local_thi_learning_companions/inform_tutors_about_unanswered_questions_after_x_days',
            get_string('setting_inform_tutors_about_unanswered_questions_after_x_days', 'local_thi_learning_companions'),
            get_string('configinform_tutors_about_unanswered_questions_after_x_days', 'local_thi_learning_companions'),
        14
        )
    );

    // Inform_tutors_about_unanswered_questions_after_x_days.
    $settings->add(new admin_setting_configtext('local_thi_learning_companions/tutorrole_shortname',
        get_string('setting_tutorrole_shortname', 'local_thi_learning_companions'),
        get_string('configtutorrole_shortname', 'local_thi_learning_companions'),
        'teacher'
    ));

    $category = new admin_category('lcconfig', get_string('adminareaname', 'local_thi_learning_companions'));
    if (!$ADMIN->locate('lcconfig')) { // Avoids "duplicate admin page name" warnings.
        $ADMIN->add('root', $category);
    }
    $ADMIN->add('lcconfig', $settings);
}
