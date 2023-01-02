<?php
namespace local_learningcompanions;
function get_chat_reactscript_path() {
    global $CFG;
    $reactscript = glob( $CFG->dirroot . '/local/learningcompanions/js/react/build/assets/index*.js');
    $reactscript = $reactscript[0];
    $reactscript = '/local/learningcompanions/js/react/build/assets/' . pathinfo($reactscript, PATHINFO_BASENAME);

    return $reactscript;
}

/**
 * @param $data
 * @param $form
 * @return array|bool[]
 */
function chat_handle_submission($data, $form) {
    require_once __DIR__ . '/classes/chat_post_form.php';
    require_once __DIR__ . '/classes/chat.php';
    try {
        $data->message = $data->message["text"];
        \local_learningcompanions\chats::post_comment($data, $form, chat_post_form::editor_options(0));
        return ["success" => true];
    } catch(\Exception $e) {
        return ["success" => false, "error" => $e->getMessage()];
    }
}
