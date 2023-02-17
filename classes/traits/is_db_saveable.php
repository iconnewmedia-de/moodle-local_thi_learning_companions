<?php

namespace local_learningcompanions\traits;

trait is_db_saveable {
    public function save() {
        global $DB;

        if ($this->id) {
            $DB->update_record(self::get_table_name(), $this->toObject());
        } else {
            $this->id = $DB->insert_record(self::get_table_name(), $this->toObject());
        }
    }

    private function toObject() {
        return (object) get_object_vars($this);
    }

    public static function find($id): self {
        global $DB;

        $record = $DB->get_record(static::get_table_name(), ['id' => $id], '*', MUST_EXIST);


        return static::from_record($record);
    }

    abstract static function get_table_name(): string;
    abstract static function from_record($record);
}
