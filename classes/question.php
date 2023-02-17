<?php

namespace local_learningcompanions;

use local_learningcompanions\traits\is_db_saveable;

class question {
    use is_db_saveable;

    private $id;
    private $askedby;
    private $mentorid;
    private $question;
    private $title;
    private $topic;
    private $timecreated;
    private $timeclosed;

    private static function get_table_name(): string {
        return 'lc_mentor_questions';
    }

    public function __construct(int $askedby, int $mentorid, string $question, string $title, int $topic) {
        $this->askedby = $askedby;
        $this->mentorid = $mentorid;
        $this->question = $question;
        $this->title = $title;
        $this->topic = $topic;
        $this->timecreated = time();
    }

    public static function ask_new_open_question(string $question, string $title, int $topic) {
        global $USER;

        $new_question = new self($USER->id, 0, $question, $title, $topic);
        $new_question->save();
        return $new_question;
    }

    public static function ask_new_question(string $question, string $title, int $topic, int $mentorid) {
        global $USER;

        $new_question = new self($USER->id, $mentorid, $question, $title, $topic);
        $new_question->save();
        return $new_question;
    }

    private static function from_record($record): self {
        $question = new self($record->askedby, $record->mentorid, $record->question, $record->title, $record->topic);
        $question->id = $record->id;
        $question->timecreated = $record->timecreated;
        $question->timeclosed = $record->timeclosed;

        return $question;
    }

    public function mark_closed(): self {
        $this->timeclosed = time();
        return $this;
    }
}
