<?php
namespace local_learningcompanions;

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
