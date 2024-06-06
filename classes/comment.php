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
namespace local_thi_learning_companions;

/**
 * Comment objects hold an entry from thi_lc_chat_comment along with the attachments for that comment.
 */
class comment {
    /**
     * @var $id
     */
    public $id;
    /**
     * @var $chatid
     */
    public $chatid;
    /**
     * @var $userid
     */
    public $userid;
    /**
     * @var $comment
     */
    public $comment;
    /**
     * @var $flagged
     */
    public $flagged;
    /**
     * @var $totalscore
     */
    public $totalscore;
    /**
     * @var $timecreated
     */
    public $timecreated;
    /**
     * @var $timemodified
     */
    public $timemodified;

    /**
     * Creates a comment object
     * @param $id
     * @throws \coding_exception
     * @throws \dml_exception
     */
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
