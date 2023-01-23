<?php
namespace local_learningcompanions;
require_once dirname(__DIR__). '/lib.php';

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
    public $createdby_fullname;
    /**
     * @var string
     */
    public $createdby_profileurl;
    /**
     * @var array
     */
    public $keywords;
    /**
     * @var string
     */
    public $keywords_list;
    /**
     * @var int
     */
    public $timecreated;
    /**
     * @var string
     */
    public $timecreated_dmY;
    /**
     * @var string
     */
    public $timecreated_userdate;
    /**
     * @var int
     */
    public $timemodified;
    /**
     * @var string
     */
    public $timemodified_dmY;
    /**
     * @var string
     */
    public $timemodified_userdate;
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
//    public $thumbnail;
    /**
     * @var int
     */
    public $latestcomment = null;
    /**
     * @var string
     */
    public $latestcomment_userdate;
    /**
     * @var int
     */
    protected $earliestcomment = null; // we seldom need these, so we only get them on demand with magic getter
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
    public $currentUserIsMember;

    /**
     * @var chat
     */
    private $chat;

    public $last_active_time;
    /**
     * @var string
     */
    public $last_active_time_dmy;
    /**
     * @var false|int
     */
    public $last_active_userid;
    /**
     * @var bool
     */
    public $may_edit;

    public function __construct($groupid, $userid = null) {
        global $DB, $CFG, $USER;
        if (is_null($userid)) {
            $userid = $USER->id;
        }
        $this->userid = $userid;
        $this->id = $groupid;
        $group = $DB->get_record('lc_groups', ['id' => $groupid]);
        $user = $DB->get_record('user', ['id' => $group->createdby]);
        $chat = $DB->get_record('lc_chat', ['relatedid' => $groupid, 'chattype' => 1]);
        foreach($group as $key => $value) {
            $this->$key = $value;
        }

        $this->createdby_fullname = fullname($user);
        $this->createdby_profileurl = $CFG->wwwroot.'/user/profile.php?id='.$user->id;

        $this->latestcomment = $DB->get_field('lc_chat_comment', 'MAX(timecreated)', ['chatid' => $chat->id, 'timedeleted' => null]);

        $this->timecreated_userdate = $this->timecreated > 0 ? userdate($this->timecreated) : '-';
        $this->timecreated_dmY =  $this->timecreated > 0 ? date('d.m.Y', $this->timecreated) : '-';
        $this->timemodified_userdate = $this->timemodified > 0 ? userdate($this->timemodified) : '-';
        $this->timemodified_dmY =  $this->timemodified > 0 ? date('d.m.Y', $this->timemodified) : '-';
        $this->latestcomment_userdate = $this->latestcomment > 0 ? userdate($this->latestcomment) : '-';
        if (date('d.m.Y', $this->latestcomment) === date('d.m.Y', time())){
            $this->latestcomment_dmY = date('H:i', $this->latestcomment);
        } elseif ($this->latestcomment > 0) {
            $this->latestcomment_dmY = date('d.m.Y', $this->latestcomment);
        } else {
            $this->latestcomment_dmY = '-';
        }
        $this->closedgroupicon = $this->closedgroup == 1 ? '<i class="icon fa fa-check"></i>' : '';
        $shortdescription = strip_tags($this->description);
        $this->shortdescription = substr($shortdescription, 0, 50);
        if (strlen($shortdescription) > 50) {
            $this->shortdescription .= " ...";
        }
        $this->chatid = $chat->id;
        $this->chat = new chat($this->id);
        $this->last_active_time = $this->chat->get_last_active_time() ?? 0;
        $this->last_active_time_dmy = !$this->last_active_time ? '-' : date('d.m.Y',$this->last_active_time);
        $this->last_active_userid = $this->chat->get_last_active_userid();

        $this->get_image();
        $this->get_imageurl();
        $this->get_groupmembers();
        $this->get_membercount();
        $this->get_admins();
        $this->get_course();
//        $this->get_keywords();
        $this->get_keywords_list();
        $this->may_edit = has_capability('local/learningcompanions:group_manage', \context_system::instance()) || array_key_exists($USER->id, $this->admins);
        // ICTODO: fetch course and course category along with relevant metadata from course and course category, like topic and such
    }

    /**
     * use magic functions, so we can access data that only needs to be read on the fly without calling methods
     * lazy loading type of thing
     * @param $name
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

    public function is_user_admin(int $userId) {
        $isAdmin = false;
        foreach ($this->admins as $admin) {
            if ((int)$admin->id === $userId) {
                $isAdmin = true;
                break;
            }
        }
        return $isAdmin;
    }

    public function is_user_member(int $userId) {
        $isMember = false;
        foreach ($this->groupmembers as $member) {
            if ((int)$member->id === $userId) {
                $isMember = true;
                break;
            }
        }
        return $isMember;
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
                    FROM {lc_chat} chat ON chat.relatedid = ? AND chat.chattype = 1
               LEFT JOIN {lc_chat_comment} posts ON posts.chatid = chat.id
                                                 GROUP BY chat.id";
        $result = $DB->get_record_sql($query, array($this->id));
        if (!$result) {
            $this->earliestcomment = 0;
        } else {
            $this->earliestcomment = $result->earliestcomment;
        }
        return $this->earliestcomment;
    }

    /**
     * @return int
     * @throws \dml_exception
     */
    protected function get_timestamp_of_my_latestcomment() {
        global $DB;
        if (!is_null($this->mylatestcomment)) {
            return $this->mylatestcomment;
        }
        $query = "SELECT MAX(posts.timecreated) AS mylatestcomment
                    FROM {lc_chat} chat ON chat.relatedid = ? AND chat.chattype = 1
               LEFT JOIN {lc_chat_comment} posts ON posts.chatid = chat.id AND posts.userid = ?
                                                 GROUP BY chat.id";
        $result = $DB->get_record_sql($query, array($this->id, $this->userid));
        if (!$result) {
            $this->mylatestcomment = 0;
        } else {
            $this->mylatestcomment = $result->mylatestcomment;
        }
        return $this->mylatestcomment;
    }

    /**
     * @return int
     * @throws \dml_exception
     */
    protected function get_timestamp_of_my_earliestcomment() {
        global $DB;
        if (!is_null($this->myearliestcomment)) {
            return $this->myearliestcomment;
        }
        $query = "SELECT MIN(posts.timecreated) AS myearliestcomment
                    FROM {lc_chat} chat ON chat.relatedid = ? AND chat.chattype = 1
               LEFT JOIN {lc_chat_comment} posts ON posts.chatid = chat.id AND posts.userid = ?
                                                 GROUP BY chat.id";
        $result = $DB->get_record_sql($query, array($this->id, $this->userid));
        if (!$result) {
            $this->myearliestcomment = 0;
        } else {
            $this->myearliestcomment = $result->myearliestcomment;
        }
        return $this->myearliestcomment;
    }

    /**
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
                    JOIN {lc_group_members} gm ON gm.userid = u.id AND u.deleted = 0
                   WHERE gm.groupid = ?',
            array($this->id)
        );
        foreach($groupmembers as $key => $member) {
            $groupmembers[$key]->password = '';
        }
        $this->groupmembers = $groupmembers;
        if (array_key_exists($USER->id, $this->groupmembers)) {
            $this->currentUserIsMember = true;
        }
        return $this->groupmembers;
    }

    /**
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
                    FROM {lc_keywords} k
                    JOIN {lc_groups_keywords} gk ON gk.groupid = ? AND gk.keywordid = k.id',
            [$this->id]
        );
        $this->keywords = array_keys($keywords);
        return $this->keywords;
    }

    public function get_keywords_list() {
        $keywords_list = $this->get_keywords();
        $keywords_list = implode(', ', $keywords_list);
        $this->keywords_list = $keywords_list;
        return $keywords_list;
    }

    /**
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
        $files = $fs->get_area_files($context->id, 'local_learningcompanions', 'groupimage', $this->id);
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
     * @return \moodle_url|string
     */
    protected function get_imageurl() {
        global $CFG;
        if (!is_null($this->imageurl)) {
            return $this->imageurl;
        }
        $file = $this->get_image();
        if (!($file instanceof \stored_file)) {
            $this->imageurl = $CFG->wwwroot . '/local/learningcompanions/pix/group.svg';
            return '';
        }
        $imageurl = \moodle_url::make_file_url('/pluginfile.php', "/" . $file->get_contextid() . "/local_learningcompanions/groupimage/" .
            $file->get_itemid() . "/" . $file->get_filename());
        $this->imageurl = (String)$imageurl;
        return $this->imageurl;
    }

    /**
     * @return false|mixed|object|\stdClass|null
     * @throws \dml_exception
     */
    protected function get_course() {
        global $DB;
        if (!is_null($this->course)) {
            return $this->course;
        }
        if (!empty($this->courseid)) {
            $this->course = $DB->get_record('course', array('id' => $this->courseid));
        } else {
            $this->course = false;
        }
        return $this->course;
    }

    /**
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
            $cm = $DB->get_record('course_modules', array('id' => $this->cmid));
            if ($cm) {
                $module = $DB->get_record('modules', array('id' => $cm->module));
                $this->cm = get_coursemodule_from_id($module->name, $this->cmid);
            }
        }
        return $this->cm;
    }

    /**
     * @return array
     * @throws \dml_exception
     */
    protected function get_admins() {
        if (!is_null($this->admins)) {
            return $this->admins;
        }
        global $DB, $CFG, $OUTPUT;

        $sql = 'SELECT u.*,
                       gm.joined
                  FROM {lc_group_members} gm
             LEFT JOIN {user} u ON u.id = gm.userid
                 WHERE gm.groupid = ?
                   AND gm.isadmin = 1';

        $admins = $DB->get_records_sql($sql, [$this->id]);
        foreach ($admins as $admin) {
            $admin->fullname = fullname($admin);
            $admin->profileurl = $CFG->wwwroot.'/user/profile.php?id='.$admin->id;
            unset($admin->password);
            $admin->status = get_user_status($admin->id);
            $admin->userpic = $OUTPUT->user_picture($admin, [
                'link' => false, 'visibletoscreenreaders' => false,
                'class' => 'userpicture'
            ]);
        }

        $this->admins = array_values($admins);
        return $this->admins;
    }

    public function get_last_comment() {
        global $DB;
        $chatid = chats::get_chat_of_group($this->id);
        if (false === $chatid) {
            return '';
        }
        $lastcomment = $DB->get_record_sql(
            'SELECT comment
                    FROM {lc_chat_comment}
                    WHERE chatid = ?
                    ORDER BY timecreated DESC, id DESC
                    LIMIT 1',
            array($chatid)
        );
        if (false === $lastcomment) {
            return '';
        }
        return $lastcomment->comment;
    }

    public function get_chat() {
        return $this->chat;
    }
}
