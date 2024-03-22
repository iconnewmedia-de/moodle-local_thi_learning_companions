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
define('AJAX_SCRIPT', true);
require_once(dirname(__DIR__, 3). '/config.php');
require_login();
// AJAX script for reporting a chat message that is abusive/racist/sexist/whatever.
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

require_login();
global $USER;
$commentid = required_param('commentid', PARAM_INT);

try {
    local_thi_learning_companions\chats::flag_comment($commentid);
    http_response_code(200);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
