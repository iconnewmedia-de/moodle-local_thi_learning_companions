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
 * @package     local_learningcompanions
 * @category    admin
 * @copyright   2022 ICON Vernetzte Kommunikation GmbH <info@iconnewmedia.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    global $DB;
    $rootcategories = $DB->get_records('course_categories', array('parent' => 0), 'sortorder');

    $options = array();
    foreach($rootcategories as $rootcategory) {
        $options[$rootcategory->id] = $rootcategory->name;
        $subcategories = $DB->get_records('course_categories', array('parent' => $rootcategory->id), 'sortorder');
        foreach($subcategories as $subcategory) {
            $options[$subcategory->id] = $rootcategory->name . ' &gt; ' . $subcategory->name;
        }
    }
    $settings = new admin_settingpage( 'local_learningcompanions', get_string('learningcompanions_settings', 'local_learningcompanions') );

    $settings->add(new admin_setting_configtext('local_learningcompanions/button_css_selector', get_string('button_css_selector', 'local_learningcompanions'),
        get_string('configbuttoncssselector', 'local_learningcompanions'), '.activityinstance, .activity-item'));
    $settings->add(new admin_setting_configtext('local_learningcompanions/button_bg', get_string('button_bg_color', 'local_learningcompanions'),
        get_string('configbuttonbg', 'local_learningcompanions'), '#333'));
    $settings->add(new admin_setting_configtext('local_learningcompanions/button_color', get_string('button_text_color', 'local_learningcompanions'),
        get_string('configbuttoncolor', 'local_learningcompanions'), '#fff'));
    $settings->add(new admin_setting_configtext('local_learningcompanions/button_radius', get_string('button_radius', 'local_learningcompanions'),
        get_string('configbuttonradius', 'local_learningcompanions'), '20', PARAM_INT));
    $settings->add(new admin_setting_configtext('local_learningcompanions/groupimage_maxbytes', get_string('groupimage_maxbytes', 'local_learningcompanions'),
        get_string('configgroupimagemaxbytes', 'local_learningcompanions'), 1000000, PARAM_INT));

    $settings->add(new admin_setting_configtext('local_learningcompanions/commentactivities',
        get_string('setting_commentactivities', 'local_learningcompanions'),
        get_string('configcommentactivities', 'local_learningcompanions'),
    'assign,assignment,book,choice,data,feedback,folder,glossary,h5pactivity,lesson,lit,quiz,resource,page,scorm,survey,workshop'
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

    $category = new admin_category('lcconfig', get_string('adminareaname', 'local_learningcompanions'));
    if (!$ADMIN->locate('lcconfig')) { // avoids "duplicate admin page name" warnings
        $ADMIN->add('root', $category);
    }
    $ADMIN->add('lcconfig', $settings);
}
