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
namespace local_thi_learning_companions\traits;

trait is_db_saveable {
    public function save() {
        global $DB;

        if ($this->id) {
            $DB->update_record(self::get_table_name(), $this->to_object());
        } else {
            $this->id = $DB->insert_record(self::get_table_name(), $this->to_object());
        }
    }

    private function to_object() {
        return (object) get_object_vars($this);
    }

    public static function find($id): self {
        global $DB;
        $record = $DB->get_record(static::get_table_name(), ['id' => $id], '*', MUST_EXIST);
        return static::from_record($record);
    }

    abstract public static function get_table_name(): string;
    abstract public static function from_record($record);
}
