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
namespace local_learningcompanions;
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");
class external extends \external_api {
    public static function list_nuggets($courseid, $query) {
        global $DB;
        $params = self::validate_parameters(self::list_nuggets_parameters(),
            array(
                'courseid' => $courseid,
                'query' => $query,
            ));
        $courseid = $params['courseid'];
        $query = $params['query'];

        file_put_contents(__DIR__ . "/logexternalapi.txt", "called at " . date('d.m.Y H:i:s') . "\r\n", FILE_APPEND);
        if (is_null($courseid)) {
            return [];
        }
        $cminfo = \course_modinfo::instance($courseid);
        $return = [];
        $cms = $cminfo->get_cms();
        foreach($cms as $cm) {
            if (empty($query) || stripos($cm->name, $query) > -1) {
                $return[] = ["id" => $cm->id, "name" => $cm->name];
            }
        }
        return $return;
    }
    public static function list_nuggets_parameters() {
        return new \external_function_parameters(
            array(
                'courseid' => new \external_value(PARAM_INT, 'The course id', VALUE_OPTIONAL, null),
                'query' => new \external_value(PARAM_TEXT, 'The text to search for', VALUE_OPTIONAL, null)
            ),
            '',
            VALUE_OPTIONAL
        );
    }
    public static function list_nuggets_returns() {
        return new \external_multiple_structure(
            new \external_single_structure([
                'id'    => new \external_value(PARAM_INT, 'ID of the context'),
                'name'  => new \external_value(PARAM_NOTAGS, 'The context name')
            ])
        );
    }
}