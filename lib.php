<?php
/**
 * @param array $comments
 * @param string $area
 * @return mixed
 * @throws coding_exception
 * @throws dml_exception
 */
function local_learningcompanions_get_attachments_of_chat_comments(array $comments, string $area) {
    // ICTODO: also get inline attachments
    if (empty($comments)) {
        return [];
    }
    $itemids = array_keys($comments);
    $filestorage = get_file_storage();
    $context = \context_system::instance();

    $files = $filestorage->get_area_files(
        $context->id,
        'local_learningcompanions',
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
        $fileurl = \moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename(), false);
        $fileurl = $fileurl->out();
        $attachment = [];
        $attachment["url"] = $fileurl;
        $attachment["filename"] = $file->get_filename();
        $attachment["filesize"] = $file->get_filesize();
//        $carry[$itemid] = array_merge($carry[$itemid], [$file]);
        $carry[$itemid] = array_merge($carry[$itemid], [$attachment]);
        return $carry;
    }, $filesbyid);
}

function local_learningcompanions_extend_navigation(global_navigation $nav) {
    return; // ICUNDO
    if (has_capability('tool/learningcompanions:manage', context_system::instance())) {
        global $CFG, $PAGE;
        $rootNode = $nav->find('home', $nav::TYPE_ROOTNODE)->parent;
        $url = new moodle_url('/admin/tool/learningcompanions/index.php');
        $node = $rootNode->add(get_string('lcadministration', 'local_learningcompanions'), $url, $nav::TYPE_ROOTNODE, null, 'learningcompanions', new pix_icon('i/nav-icon', '', 'tool_learningcompanions'));
        $subNavigationItems = array(
            'comments',
            'groups'
        );
        foreach($subNavigationItems as $subNavigationItem) {
            $node->add(get_string('lcadministration_' .$subNavigationItem, 'local_learningcompanions'), new moodle_url($CFG->wwwroot . '/admin/tool/learningcompanions/'.$subNavigationItem.'/index.php'), null, null, $subNavigationItem);
        }
        if (strpos($PAGE->url, 'admin/tool/learningcompanions') > -1) {
            $node->force_open();
        }
        $node->showinflatnavigation = true;
    }
}

function local_learningcompanions_pluginfile($course, $record, $context, $filearea, $args, $forcedownload, array $options=array()) {

    if ($context->contextlevel != CONTEXT_SYSTEM) {
        send_file_not_found();
    }

    $areaWhitelist = array('groupimage', 'attachments', 'message');
    if (!in_array($filearea, $areaWhitelist)) {
        send_file_not_found();
    }
    $groupid = (int)array_shift($args);

    $fs = get_file_storage();

    $filename = array_pop($args);
    $filepath = $args ? '/'.implode('/', $args).'/' : '/';
    $context = context_system::instance();

    if (!$file = $fs->get_file($context->id, 'local_learningcompanions', $filearea, $groupid, $filepath, $filename) or $file->is_directory()) {
        send_file_not_found();
    }

    // NOTE: it woudl be nice to have file revisions here, for now rely on standard file lifetime,
    //       do not lower it because the files are dispalyed very often.
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
function local_learningcompanions_get_user_status(int $userid = null): array {
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
    // 'Please do not disturb' => 'pleasedonotdisturb'
    $statusIcon = str_replace(' ', '', $status);
    $statusIcon = strtolower($statusIcon);
    $statusfield = format_string($statusfield, true, ['context' => context_system::instance()]);
    return array($statusIcon, $statusfield);
}

function set_user_status($status, $userid = null) {
    global $CFG, $DB, $USER;

    require_once($CFG->dirroot.'/user/profile/lib.php');

    $userid = is_null($userid) ? $USER->id : $userid;
    $user = $DB->get_record('user', array('id' => $userid));

    // ICTODO
}


/**
 * gets called as a service from JS in group.js, handleGroupInviteButton:
 * const templatePromise = Fragment.loadFragment('local_learningcompanions', 'invitation_form', groupId, {});
 * Moodle dynamically constructs the function to call - so don't believe your IDE when it tells you that this is unused!
 * Serve the manual enrol users form as a fragment.
 *
 * @param array $args List of named arguments for the fragment loader.
 * @return string
 */
function local_learningcompanions_output_fragment_invitation_form($args) {
    $args = (object) $args;
    $context = $args->context;
    $o = '';
    require_once __DIR__ . '/classes/forms/select_users_to_invite_form.php';
    $mform = new local_learningcompanions\select_users_to_invite_form(null, $args);

    ob_start();
    $mform->display();
    $o .= ob_get_contents();
    ob_end_clean();

    return $o;
}
