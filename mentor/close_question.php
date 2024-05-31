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
require_once(dirname(__DIR__, 3) . '/config.php');

require_login();

global $USER;

$questionid = required_param('id', PARAM_INT);

$question = \local_thi_learning_companions\question::find($questionid);

if ($question->get_askedby() !== (int)$USER->id && !is_siteadmin($USER->id)) {
    redirect(
        new moodle_url('/local/thi_learning_companions/mentor/', ),
        get_string('not_question_owner', 'local_thi_learning_companions')
    );
    die();
}

$question->mark_closed()->save();

redirect(
    new moodle_url('/local/thi_learning_companions/mentor/', ),
    get_string('question_closed', 'local_thi_learning_companions')
);
