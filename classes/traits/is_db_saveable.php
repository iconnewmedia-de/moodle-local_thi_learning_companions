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

namespace local_thi_learning_companions\traits;

/**
 * collection of functions for classes whose objects we want to save to database
 */
trait is_db_saveable {
    /**
     * updates existing records, inserts new records
     * @return void
     * @throws \dml_exception
     */
    public function save() {
        global $DB;

        if ($this->id) {
            $DB->update_record(self::get_table_name(), $this->to_object());
        } else {
            $this->id = $DB->insert_record(self::get_table_name(), $this->to_object());
        }
    }

    /**
     * returns the object variables as object
     * @return object
     */
    private function to_object() {
        return (object) get_object_vars($this);
    }

    /**
     * returns a record from the database for the given id
     * @param int $id
     * @return is_db_saveable|\local_thi_learning_companions\question
     * @throws \dml_exception
     */
    public static function find($id): self {
        global $DB;
        $record = $DB->get_record(static::get_table_name(), ['id' => $id], '*', MUST_EXIST);
        return static::from_record($record);
    }

    /**
     * returns the table name
     * @return string
     */
    abstract public static function get_table_name(): string;

    /**
     * creates an instance from a db record
     * @param \stdClass $record
     * @return mixed
     */
    abstract public static function from_record($record);
}
