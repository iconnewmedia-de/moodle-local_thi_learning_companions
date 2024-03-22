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
use mod_bigbluebuttonbn\local\helpers\files;
use stdClass;
use context_system;
use moodle_url;
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . "/mod/bigbluebuttonbn/classes/instance.php");;
class instance extends \mod_bigbluebuttonbn\instance {

    public function __construct(int $groupid, stdClass $instancedata) {
        $this->groupid = $groupid;
        $this->instancedata = $instancedata;
        $this->cm = null;
    }

    public function get_from_groupid(int $groupid) {
        global $DB;

        $sql = "SELECT *
      FROM {thi_lc_bbb}
     WHERE groupid = :groupid";

        $instancedata = $DB->get_record_sql($sql, [
            'groupid' => $groupid
        ]);

        if (empty($result)) {
            $this->meeting->create_meeting();
        }
        return new self($groupid, $instancedata);
    }

    /**
     * @return array
     * @throws \dml_exception
     */
    public static function create_meeting($groupid): stdClass {
        global $DB, $USER;
        // ICTODO: das müsste eigentlich bei meeting.php hängen und ganz anders aussehen!
        $data = [];
        $meetingid = self::get_new_meetingid();
        $data["groupid"] = $groupid;
        $data["meetingid"] = $meetingid;
        $data["moderatorpass"] = uniqid();
        $data["viewerpass"] = uniqid();
        $data["wait"] = 0;
        $data["record"] = 1;
        $data["recordallfromstart"] = 0;
        $data["recordhidebutton"] = 0;
        $data["voicebridge"] = 0;
        $data["openingtime"] = 0;
        $data["closingtime"] = 0;
        $data["timemodified"] = 0;
        $data["timecreated"] = time();
        $data["presentation"] = "";
        $data["userlimit"] = 0;
        $data["recordings_html"] =0;
        $data["recordings_deleted"] = 1;
        $data["recordings_imported"] = 0;
        $data["recordings_preview"] = 1;
        $data["muteonstart"] = 0;
        $data["disablecam"] = 0;
        $data["disablemic"] = 0;
        $data["disableprivatechat"] = 0;
        $data["disablepublicchat"] = 0;
        $data["disablenote"] = 0;
        $data["hideuserlist"] = 0;
        $data["lockedlayout"] = 0;
        $data["lockonjoin"] = 1;
        $data["moderatorid"] = $USER->id;

        $data["id"] = $DB->insert_record('thi_lc_bbb', $data);
        $data = (object)$data;
        return $data;
        // ICTODO: replace hardcoded values with values from config
    }

    /**
     * Get the meeting id for this meeting.
     *
     * @param null|int $groupid
     * @return string
     */
    public function get_meeting_id(?int $groupid = null): string {
        $baseid = sprintf(
            '%s-%s-%s',
            $this->get_instance_var('meetingid'),
            $this->groupid,
            $this->get_instance_var('id')
        );

        return sprintf('%s[%s]', $baseid, $this->groupid);
    }

    /**
     * Get the logout URL used to log out of the meeting.
     *
     * @return moodle_url
     */
    public function get_logout_url(): moodle_url {
        return new moodle_url('/local/thi_learning_companions/chat.php', [
            'id' => $this->groupid,
        ]);
    }

    protected static function get_new_meetingid() {
        global $DB;
        do {
            $encodedseed = sha1(uniqid());
            $meetingid = (string) $DB->get_field('thi_lc_bbb', 'meetingid', ['meetingid' => $encodedseed]);
        } while ($meetingid == $encodedseed);
        /*
         * müsste eigentlich eher so sein:
         *  $baseid = sprintf(
            '%s-%s-%s',
            $this->get_instance_var('meetingid'),
            $this->get_course_id(),
            $this->get_instance_var('id')
        );

        if ($groupid === null) {
            $groupid = $this->get_group_id();
        }

        return sprintf('%s[%s]', $baseid, $groupid);
         */
        return $encodedseed;
    }

    /**
     * Whether the user is a session moderator.
     *
     * @return bool
     */
    public function is_moderator(): bool {
        global $USER;
        return $USER->id === $this->get_instance_var("moderatorid");
    }

    /**
     * Whether this user can join the conference.
     *
     * This checks the user right for access against capabilities and group membership
     *
     * @return bool
     */
    public function can_join(): bool {
        return groups::may_view_group($this->groupid);
    }

    /**
     * Generate Presentation URL.
     *
     * @param bool $withnonce The generated url will have a nonce included
     * @return array|null
     */
    protected function do_get_presentation_with_nonce(bool $withnonce): ?array {
        if ($this->has_ended()) {
            return files::get_presentation(
                context_system::instance(),
                $this->get_instance_var('presentation'),
                null,
                $withnonce
            );
        } else if ($this->is_currently_open()) {
            $context = context_system::instance();
            $presentation = $this->get_instance_var('presentation');
            $instanceid = $this->get_instance_id();
            return files::get_presentation(
                $context,
                $presentation,
                $instanceid,
                $withnonce
            );
        } else {
            return [];
        }
    }

    /**
     * Get the group name for the current group, if a group has been set.
     *
     * @return null|string
     */
    public function get_group_name(): ?string {
        global $DB;
        return $DB->get_field('thi_lc_groups', 'name', array('id' => $this->groupid));
    }

    /**
     * @return int
     */
    public function get_group_id():int {
        return $this->groupid;
    }

    /**
     * Get the meeting description with the pluginfile URLs optionally rewritten.
     *
     * @param bool $rewritepluginfileurls
     * @return string
     */
    public function get_meeting_description(bool $rewritepluginfileurls = false): string {
        global $DB;
        $description = $DB->get_field('thi_lc_groups', 'description', array('id' => $this->groupid));
        return $description;
    }
}