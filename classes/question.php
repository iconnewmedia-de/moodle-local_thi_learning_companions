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

    public function __construct(int $askedby, int $mentorid, string $question, string $title, string $topic) {
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

    /**
     * @param $questionid
     * @return \local_learningcompanions\question
     * @throws \dml_exception
     */
    public static function get_question_by_id($questionid) {
        global $DB;
        $record = $DB->get_record('lc_mentor_questions', array('id' => $questionid));
        // ICTODO: make sure the user has the right to see the question
        return self::from_record($record);
    }

    public function to_array() {
        return array(
            'id' => $this->id,
            'askedby' => $this->askedby,
            'mentorid' => $this->mentorid,
            'question' => $this->question,
            'title' => $this->title,
            'topic' => $this->topic,
            'timecreated' => $this->timecreated,
            'timeclosed' => $this->timeclosed,
        );
    }

    /**
     * @return int
     */
    public function get_askedby(): int {
        return $this->askedby;
    }

    public function is_closed() {
        return $this->timeclosed !== null;
    }

    public function get_id() {
        return $this->id;
    }
}
