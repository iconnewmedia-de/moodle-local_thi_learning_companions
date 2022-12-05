<?php
function local_learningcompanions_extend_settings_navigation() {
    global $PAGE;
    if (!isloggedin()){
        return;
    }
    $config = get_config('local_learningcompanions');
    $params = array(
        isset($config->button_css_selector)?$config->button_css_selector:'.activityinstance, .activity-item',
        isset($config->button_bg_color)?$config->button_bg_color:'#333',
        isset($config->button_text_color)?$config->button_text_color:'#fff',
        isset($config->button_radius)?$config->button_radius:'20',
    );
    $PAGE->requires->js_call_amd('local_learningcompanions/learningcompanions', 'init', $params);
}

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
 * @return string
 * @throws dml_exception
 */
function get_user_status(int $userid = null, bool $readable = false): string {
    global $CFG, $DB, $USER;

    require_once($CFG->dirroot.'/user/profile/lib.php');

    $userid = is_null($userid) ? $USER->id : $userid;
    $user = $DB->get_record('user', ['id' => $userid]);
    profile_load_data($user);

    $statusfield = $user->profile_field_lc_user_status;
    $status = explode('<span lang="en" class="multilang">', $statusfield)[1];
    $status = explode('</span>', $status)[0];

    // 'Please do not disturb' => 'pleasedonotdisturb'
    if (!$readable) {
        $status = str_replace(' ', '', $status);
        $status = strtolower($status);
    }

    return $status;
}

function set_user_status($status, $userid = null) {
    global $CFG, $DB, $USER;

    require_once($CFG->dirroot.'/user/profile/lib.php');

    $userid = is_null($userid) ? $USER->id : $userid;
    $user = $DB->get_record('user', array('id' => $userid));

    // ICTODO
}
