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

/**
 * @param array $comments
 * @param string $area
 * @return mixed
 * @throws coding_exception
 * @throws dml_exception
 */
function local_thi_learning_companions_get_attachments_of_chat_comments(array $comments, string $area) {
    if (empty($comments)) {
        return [];
    }
    $itemids = array_keys($comments);
    $filestorage = get_file_storage();
    $context = \context_system::instance();

    $files = $filestorage->get_area_files(
        $context->id,
        'local_thi_learning_companions',
        $area,
        $itemids,
        'filename',
        false
    );

    $filesbyid = array_reduce($comments, function($carry, $comment) {
        $carry[$comment->id] = [];
        return $carry;
    }, []);

    return array_reduce($files, function($carry, $file) {
        $itemid = $file->get_itemid();
        $fileurl = \moodle_url::make_pluginfile_url(
            $file->get_contextid(),
            $file->get_component(),
            $file->get_filearea(),
            $file->get_itemid(),
            $file->get_filepath(),
            $file->get_filename(),
            false
        );
        $fileurl = $fileurl->out();
        $attachment = [];
        $attachment["url"] = $fileurl;
        $attachment["filename"] = $file->get_filename();
        $attachment["filesize"] = $file->get_filesize();
        $carry[$itemid] = array_merge($carry[$itemid], [$attachment]);
        return $carry;
    }, $filesbyid);
}

function local_thi_learning_companions_extend_navigation(global_navigation $nav) {
    return; // ICUNDO.
    if (has_capability('tool/thi_learning_companions:manage', context_system::instance())) {
        global $CFG, $PAGE;
        $rootnode = $nav->find('home', $nav::TYPE_ROOTNODE)->parent;
        $url = new moodle_url('/admin/tool/thi_learning_companions/index.php');
        $node = $rootnode->add(
            get_string('lcadministration', 'local_thi_learning_companions'),
            $url,
            $nav::TYPE_ROOTNODE,
            null,
            'thi_learning_companions',
            new pix_icon('i/nav-icon', '', 'tool_thi_learning_companions')
        );
        $subnavigationitems = [
            'comments',
            'groups',
        ];
        foreach ($subnavigationitems as $subnavigationitem) {
            $node->add(
                get_string('lcadministration_' .$subnavigationitem, 'local_thi_learning_companions'),
                new moodle_url($CFG->wwwroot . '/admin/tool/thi_learning_companions/'.$subnavigationitem.'/index.php'),
                null,
                null,
                $subnavigationitem
            );
        }
        if (strpos($PAGE->url, 'admin/tool/thi_learning_companions') > -1) {
            $node->force_open();
        }
        $node->showinflatnavigation = true;
    }
}

function local_thi_learning_companions_pluginfile($course, $record, $context, $filearea, $args, $forcedownload, array $options=[]) {

    if ($context->contextlevel != CONTEXT_SYSTEM) {
        send_file_not_found();
    }

    $areawhitelist = ['groupimage', 'attachments', 'message'];
    if (!in_array($filearea, $areawhitelist)) {
        send_file_not_found();
    }
    $groupid = (int)array_shift($args);

    $fs = get_file_storage();

    $filename = array_pop($args);
    $filepath = $args ? '/'.implode('/', $args).'/' : '/';
    $context = context_system::instance();

    if (!$file = $fs->get_file(
        $context->id,
        'local_thi_learning_companions',
        $filearea,
        $groupid,
        $filepath,
        $filename)
    ) {
        send_file_not_found();
    }
    if ($file->is_directory()) {
        send_file_not_found();
    }

    // NOTE: it would be nice to have file revisions here, for now rely on standard file lifetime,
    // do not lower it because the files are dispalyed very often.
    \core\session\manager::write_close();
    send_stored_file($file, null, 0, $forcedownload = false, $options);
}

/**
 * @param $userid   int|null The user id to get the status for
 * @param $readable bool Whether to return the status as a sentence or as a class string
 *
 * @return array    first value is for css classes, second value is the actual readable value, in the user's language
 * @throws dml_exception
 */
function local_thi_learning_companions_get_user_status(int $userid = null): array {
    global $CFG, $DB, $USER;

    require_once($CFG->dirroot.'/user/profile/lib.php');
    require_once($CFG->dirroot.'/message/classes/helper.php');

    $userid = is_null($userid) ? $USER->id : $userid;
    $user = $DB->get_record('user', ['id' => $userid]);
    profile_load_data($user);

    $statusfield = $user->profile_field_lc_user_status;
    $status = explode('<span lang="en" class="multilang">', $statusfield)[1];
    $status = explode('</span>', $status)[0];
    if ($status === 'Online' && $userid !== $USER->id) {
        if (!\core_message\helper::is_online($user->lastaccess)) {
            $status = 'Offline';
        }
    }
    $statusicon = str_replace(' ', '', $status);
    $statusicon = strtolower($statusicon);
    $statusfield = format_string($statusfield, true, ['context' => context_system::instance()]);
    return [$statusicon, $statusfield];
}

function set_user_status($status, $userid = null) {
    global $CFG, $DB, $USER;

    require_once($CFG->dirroot.'/user/profile/lib.php');

    $userid = is_null($userid) ? $USER->id : $userid;
    $user = $DB->get_record('user', ['id' => $userid]);

    // ICTODO.
}

/**
 * gets called as a service from JS in group.js, handleGroupInviteButton:
 * const templatePromise = Fragment.loadFragment('local_thi_learning_companions', 'invitation_form', groupId, {});
 * Moodle dynamically constructs the function to call - so don't believe your IDE when it tells you that this is unused!
 * Serve the manual enrol users form as a fragment.
 *
 * @param array $args List of named arguments for the fragment loader.
 * @return string
 */
function local_thi_learning_companions_output_fragment_invitation_form($args) {
    global $CFG;
    $args = (object) $args;
    $context = $args->context;
    $o = '';
    require_once(__DIR__ . '/classes/forms/select_users_to_invite_form.php');
    $mform = new local_thi_learning_companions\select_users_to_invite_form(
        $CFG->wwwroot . "/local/thi_learning_companions/chat.php?groupid=" .
        intval($context->id),
        $args
    );
    return $mform->render();
}
