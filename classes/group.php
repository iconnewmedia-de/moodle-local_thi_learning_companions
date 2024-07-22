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
defined('MOODLE_INTERNAL') || die();
require_once(dirname(__DIR__). '/lib.php');

/**
 * Object that represents a group with all its corresponding data
 */
class group {
    /**
     * @var int
     */
    public $id;
    /**
     * @var \stdClass[]|null
     */
    public $admins = null;
    /**
     * @var string
     */
    public $createdbyfullname;
    /**
     * @var string
     */
    public $createdbyprofileurl;
    /**
     * @var array
     */
    public $keywords;
    /**
     * @var string
     */
    public $keywordslist;
    /**
     * @var int
     */
    public $timecreated;
    /**
     * @var string
     */
    public $timecreateddmy;
    /**
     * @var string
     */
    public $timecreateduserdate;
    /**
     * @var int
     */
    public $timemodified;
    /**
     * @var string
     */
    public $timemodifieddmy;
    /**
     * @var string
     */
    public $timemodifieduserdate;
    /**
     * @var bool
     */
    public $closedgroup;
    /**
     * @var string
     */
    public $closedgroupicon;
    /**
     * @var string
     */
    public $description;
    /**
     * @var string
     */
    public $shortdescription;
    /**
     * @var string
     */
    public $name;
    /**
     * @var int
     */
    public $courseid;
    /**
     * @var int
     */
    public $cmid;
    /**
     * @var \stdClass
     */
    public $course;
    /**
     * @var \stdClass
     */
    protected $cm;
    /**
     * @var string
     */
    public $imageurl = null;
    /**
     * @var \stored_file|false
     */
    public $image = null;
    /**
     * @var string
     */
    public $userid;
    /**
     * @var \stdClass[]
     */
    public $groupmembers = null;
    /**
     * @var int
     */
    public $membercount = null;
    /**
     * @var int
     */
    public $latestcomment = null;
    /**
     * @var string
     */
    public $latestcommentdmy = null;
    /**
     * @var string
     */
    public $latestcommentuserdate;
    /**
     * @var int
     */
    protected $earliestcomment = null; // We seldom need these, so we only get them on demand with magic getter.
    /**
     * @var int
     */
    protected $myearliestcomment = null;
    /**
     * @var int
     */
    protected $mylatestcomment = null;
    /**
     * @var bool
     */
    public $currentuserismember;

    /**
     * @var chat
     */
    private $chat;

    /**
     * @var false|int|mixed
     */
    public $lastactivetime;
    /**
     * @var string
     */
    public $lastactivetimedmy;
    /**
     * @var false|int
     */
    public $lastactiveuserid;
    /**
     * @var bool
     */
    public $mayedit;

    /**
     * @var bool
     */
    public $isadmin;

    /**
     * Constructor
     * @param int $groupid
     * @param int|null $userid
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function __construct($groupid, $userid = null) {
        global $DB, $CFG, $USER;

        if (is_null($userid)) {
            $userid = $USER->id;
        }
        $this->userid = $userid;
        $this->id = $groupid;
        $group = $DB->get_record('local_thi_learning_companions_groups', ['id' => $groupid]);
        $user = $DB->get_record('user', ['id' => $group->createdby]);
        $chat = $DB->get_record('local_thi_learning_companions_chat',
            ['relatedid' => $groupid, 'chattype' => groups::CHATTYPE_GROUP]);
        foreach ($group as $key => $value) {
            $this->$key = $value;
        }

        $this->createdbyfullname = fullname($user);
        $this->createdbyprofileurl = $CFG->wwwroot.'/user/profile.php?id='.$user->id;

        $this->latestcomment = $DB->get_field(
            'local_thi_learning_companions_chat_comment',
            'MAX(timecreated)',
            ['chatid' => $chat->id, 'timedeleted' => null]
        );

        $this->timecreateduserdate = $this->timecreated > 0 ? userdate($this->timecreated) : '-';
        $this->timecreateddmy = $this->timecreated > 0 ? date('d.m.Y', $this->timecreated) : '-';
        $this->timemodifieduserdate = $this->timemodified > 0 ? userdate($this->timemodified) : '-';
        $this->timemodifieddmy = $this->timemodified > 0 ? date('d.m.Y', $this->timemodified) : '-';
        $this->latestcommentuserdate = $this->latestcomment > 0 ? userdate($this->latestcomment) : '-';
        if (date('d.m.Y', $this->latestcomment) === date('d.m.Y', time())) {
            $this->latestcommentdmy = date('H:i', $this->latestcomment);
        } else if ($this->latestcomment > 0) {
            $this->latestcommentdmy = date('d.m.Y', $this->latestcomment);
        } else {
            $this->latestcommentdmy = '-';
        }
        $this->closedgroupicon = $this->closedgroup == 1 ? '<i class="icon fa fa-check"></i>' : '';
        $shortdescription = strip_tags($this->description);
        $this->shortdescription = substr($shortdescription, 0, 50);
        if (strlen($shortdescription) > 50) {
            $this->shortdescription .= " ...";
        }
        $this->chatid = $chat->id;
        $this->chat = chat::create_group_chat($this->id);
        $this->lastactivetime = $this->chat->get_last_active_time() ?? 0;
        $this->lastactivetimedmy = !$this->lastactivetime ? '-' : date('d.m.Y', $this->lastactivetime);
        $this->lastactiveuserid = $this->chat->get_last_active_userid();

        $this->get_image();
        $this->get_imageurl();
        $this->get_groupmembers();
        $this->get_membercount();
        $this->get_admins();
        $this->get_course();
        $this->get_keywordslist();
        $this->mayedit = $this->isadmin || has_capability(
            'local/thi_learning_companions:group_manage',
            \context_system::instance()
            );
        // ICTODO: fetch course and course category along with metadata, like topic and such.
    }

    /**
     * use magic functions, so we can access data that only needs to be read on the fly without calling methods
     * lazy loading type of thing
     * @param string $name
     * @return array|int|void
     * @throws \dml_exception
     */
    public function __get($name) {
        $methodname = "get_" . $name;
        if (property_exists($this, $name) && method_exists($this, $methodname)) {
            return $this->$methodname();
        }
        return null;
    }

    /**
     * returns true if user is admin
     * @param int $userid
     * @return bool
     */
    public function is_user_admin(int $userid) {
        $isadmin = false;
        foreach ($this->admins as $admin) {
            if ((int)$admin->id === $userid) {
                $isadmin = true;
                break;
            }
        }
        return $isadmin;
    }

    /**
     * returns true if user is member
     * @param int $userid
     * @return bool
     */
    public function is_user_member(int $userid) {
        $ismember = false;
        foreach ($this->groupmembers as $member) {
            if ((int)$member->id === $userid) {
                $ismember = true;
                break;
            }
        }
        return $ismember;
    }

    /**
     * yes, PHPStorm thinks this method should be greyed out because it's never used
     * but it will actually get called by __get if someone tries to access $group->latestpost
     * so please don't remove this code :)
     * @return int
     * @throws \dml_exception
     */
    protected function get_timestamp_of_earliestcomment() {
        global $DB;
        if (!is_null($this->earliestcomment)) {
            return $this->earliestcomment;
        }
        $query = "SELECT MIN(posts.timecreated) AS earliestcomment
                    FROM {local_thi_learning_companions_chat} chat ON chat.relatedid = ? AND chat.chattype = 1
               LEFT JOIN {local_thi_learning_companions_chat_comment} posts ON posts.chatid = chat.id
                                                 GROUP BY chat.id";
        $result = $DB->get_record_sql($query, [$this->id]);
        if (!$result) {
            $this->earliestcomment = 0;
        } else {
            $this->earliestcomment = $result->earliestcomment;
        }
        return $this->earliestcomment;
    }

    /**
     * returns timestamp of current user's last comment
     * @return int
     * @throws \dml_exception
     */
    protected function get_timestamp_of_my_latestcomment() {
        global $DB;
        if (!is_null($this->mylatestcomment)) {
            return $this->mylatestcomment;
        }
        $query = "SELECT MAX(posts.timecreated) AS mylatestcomment
                    FROM {local_thi_learning_companions_chat} chat ON chat.relatedid = ? AND chat.chattype = 1
               LEFT JOIN {local_thi_learning_companions_chat_comment} posts ON posts.chatid = chat.id AND posts.userid = ?
                                                 GROUP BY chat.id";
        $result = $DB->get_record_sql($query, [$this->id, $this->userid]);
        if (!$result) {
            $this->mylatestcomment = 0;
        } else {
            $this->mylatestcomment = $result->mylatestcomment;
        }
        return $this->mylatestcomment;
    }

    /**
     * returns timestamp of current users earliest comment
     * @return int
     * @throws \dml_exception
     */
    protected function get_timestamp_of_my_earliestcomment() {
        global $DB;
        if (!is_null($this->myearliestcomment)) {
            return $this->myearliestcomment;
        }
        $query = "SELECT MIN(posts.timecreated) AS myearliestcomment
                    FROM {local_thi_learning_companions_chat} chat ON chat.relatedid = ? AND chat.chattype = 1
               LEFT JOIN {local_thi_learning_companions_chat_comment} posts ON posts.chatid = chat.id AND posts.userid = ?
                                                 GROUP BY chat.id";
        $result = $DB->get_record_sql($query, [$this->id, $this->userid]);
        if (!$result) {
            $this->myearliestcomment = 0;
        } else {
            $this->myearliestcomment = $result->myearliestcomment;
        }
        return $this->myearliestcomment;
    }

    /**
     * returns group members
     * @return array
     * @throws \dml_exception
     */
    protected function get_groupmembers() {
        global $USER;
        if (!is_null($this->groupmembers)) {
            return $this->groupmembers;
        }
        global $DB;
        $groupmembers = $DB->get_records_sql(
            'SELECT DISTINCT u.*, gm.isadmin
                    FROM {user} u
                    JOIN {local_thi_learning_companions_group_members} gm ON gm.userid = u.id AND u.deleted = 0
                   WHERE gm.groupid = ?',
            [$this->id]
        );
        foreach ($groupmembers as $key => $member) {
            $groupmembers[$key]->password = '';
        }
        $this->groupmembers = $groupmembers;
        if (array_key_exists($USER->id, $this->groupmembers)) {
            $this->currentuserismember = true;
        }
        return $this->groupmembers;
    }

    /**
     * returns member count
     * @return int
     * @throws \dml_exception
     */
    protected function get_membercount() {
        if (!is_null($this->membercount)) {
            return $this->membercount;
        }
        $members = $this->get_groupmembers();
        if (!is_countable($members)) {
            $this->membercount = 0;
        } else {
            $this->membercount = count($members);
        }
        return $this->membercount;
    }

    /**
     * returns the keywords for the current group
     * @return array
     * @throws \dml_exception
     */
    protected function get_keywords() {
        if (!is_null($this->keywords)) {
            return $this->keywords;
        }
        global $DB;
        $keywords = $DB->get_records_sql(
            'SELECT DISTINCT k.keyword
                    FROM {local_thi_learning_companions_keywords} k
                    JOIN {local_thi_learning_companions_groups_keywords} gk ON gk.groupid = ? AND gk.keywordid = k.id',
            [$this->id]
        );
        $this->keywords = array_keys($keywords);
        return $this->keywords;
    }

    /**
     * returns the list of keywords as comma separated string
     * @return string
     * @throws \dml_exception
     */
    public function get_keywordslist() {
        $keywordslist = $this->get_keywords();
        $keywordslist = implode(', ', $keywordslist);
        $this->keywordslist = $keywordslist;
        return $keywordslist;
    }

    /**
     * returns the group's image
     * @return object|\stored_file|null
     * @throws \coding_exception
     * @throws \dml_exception
     */
    protected function get_image() {
        if (!is_null($this->image)) {
            return $this->image;
        }
        $context = \context_system::instance();
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'local_thi_learning_companions', 'groupimage', $this->id);
        foreach ($files as $f) {
            if ($f->is_valid_image()) {
                $this->image = $f;
                return $f;
            }
        }
        $this->image = false;
        return false;
    }

    /**
     * returns the url for the group's image
     * @return \moodle_url|string
     */
    protected function get_imageurl() {
        global $CFG;
        if (!is_null($this->imageurl)) {
            return $this->imageurl;
        }
        $file = $this->get_image();
        if (!($file instanceof \stored_file)) {
            $this->imageurl = $CFG->wwwroot . '/local/thi_learning_companions/pix/group.svg';
            return '';
        }
        $imageurl = \moodle_url::make_file_url(
            '/pluginfile.php',
            "/" . $file->get_contextid() . "/local_thi_learning_companions/groupimage/" .
            $file->get_itemid() . "/" . $file->get_filename()
        );
        $this->imageurl = (String)$imageurl;
        return $this->imageurl;
    }

    /**
     * returns the course for the current group
     * @return false|mixed|object|\stdClass|null
     * @throws \dml_exception
     */
    protected function get_course() {
        global $DB;
        if (!is_null($this->course)) {
            return $this->course;
        }
        if (!empty($this->courseid)) {
            $this->course = $DB->get_record('course', ['id' => $this->courseid]);
        } else {
            $this->course = false;
        }
        return $this->course;
    }

    /**
     * returns the course module for the current group
     * @return false|object|\stdClass|null
     * @throws \coding_exception
     */
    protected function get_cm() {
        global $DB;
        if (!is_null($this->cm)) {
            return $this->cm;
        }
        $this->cm = false;
        if (!empty($this->cmid)) {
            $cm = $DB->get_record('course_modules', ['id' => $this->cmid]);
            if ($cm) {
                $module = $DB->get_record('modules', ['id' => $cm->module]);
                $this->cm = get_coursemodule_from_id($module->name, $this->cmid);
            }
        }
        return $this->cm;
    }

    /**
     * returns the group's admins
     * @return array
     * @throws \dml_exception
     */
    protected function get_admins() {
        if (!is_null($this->admins)) {
            return $this->admins;
        }
        global $DB, $CFG, $OUTPUT, $USER;
        $this->isadmin = false;

        $sql = 'SELECT u.*,
                       gm.joined
                  FROM {local_thi_learning_companions_group_members} gm
             LEFT JOIN {user} u ON u.id = gm.userid AND u.deleted = 0
                 WHERE gm.groupid = ?
                   AND gm.isadmin = 1';

        $admins = $DB->get_records_sql($sql, [$this->id]);
        foreach ($admins as $admin) {
            if ($admin->id === $USER->id) {
                $this->isadmin = true;
            }
            $admin->fullname = fullname($admin);
            $admin->profileurl = $CFG->wwwroot.'/user/profile.php?id='.$admin->id;
            unset($admin->password);
            list($admin->status, $admin->statustext) = \local_thi_learning_companions_get_user_status($admin->id);
            $admin->userpic = $OUTPUT->user_picture($admin, [
                'link' => false, 'visibletoscreenreaders' => false,
                'class' => 'userpicture',
            ]);
        }

        $this->admins = array_values($admins);
        return $this->admins;
    }

    /**
     * returns the group's last comment
     * @return string
     * @throws \dml_exception
     */
    public function get_last_comment() {
        global $DB;
        $chatid = chats::get_chat_of_group($this->id);
        if (false === $chatid) {
            return '';
        }
        $lastcomment = $DB->get_record_sql(
            'SELECT comment
                    FROM {local_thi_learning_companions_chat_comment}
                    WHERE chatid = ?
                    ORDER BY timecreated DESC, id DESC
                    LIMIT 1',
            [$chatid]
        );
        if (false === $lastcomment) {
            return '';
        }
        return $lastcomment->comment;
    }

    /**
     * returns the group's chat
     * @return chat
     */
    public function get_chat() {
        return $this->chat;
    }
}
