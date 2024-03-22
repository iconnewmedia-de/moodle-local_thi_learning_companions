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
namespace local_thi_learning_companions;

class comment {
    public $id;
    public $chatid;
    public $userid;
    public $comment;
    public $flagged;
    public $totalscore;
    public $timecreated;
    public $timemodified;
    public function __construct($id) {
        global $DB;
        $this->id = $id;
        $comment = $DB->get_record('thi_lc_chat_comment', ['id' => $id]);
        foreach ($comment as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        $this->attachments = local_thi_learning_companions_get_attachments_of_chat_comments([$comment], 'attachments');
    }
}
