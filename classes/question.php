<?php

namespace local_learningcompanions;

use local_learningcompanions\traits\is_db_saveable;

class question {
    use is_db_saveable;

    public $id;
    public $askedby;
    public $mentorid;
    public $question;
    public $title;
    public $topic;
    public $timecreated;
    public $timeclosed;

    public $last_active;
    public $last_active_dmy;

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

    /**
     * @param int $userid
     *
     * @return self[]
     * @throws \dml_exception
     */
    public static function get_all_questions_for_user(int $userid) {
        global $DB;

        $records = $DB->get_records('lc_mentor_questions', ['askedby' => $userid]);
        $questions = [];
        foreach ($records as $record) {
            $questions[] = self::find($record->id);
        }
        return $questions;
    }

    public function get_last_activity() {
        global $DB;

        if (isset($this->last_active)) {
            return $this->last_active;
        }

        if ($this->timeclosed !== null) {
            return $this->timeclosed;
        }

        $chat = $this->get_chat();

        return $this->last_active = $DB->get_field('lc_chat_comment', 'MAX(timecreated)', ['chatid' => $chat->id, 'timedeleted' => null]);
    }

    private function get_chat() {
        global $DB;

        if (isset($this->chat)) {
            return $this->chat;
        }

        return $this->chat = $DB->get_record('lc_chat', ['relatedid' => $this->id, 'chattype' => groups::CHATTYPE_MENTOR]);
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
        return $this->timeclosed !== null && intval($this->timeclosed) !== 0;
    }

    public function get_closed_time() {
        return userdate($this->timeclosed, get_string('strftimedatefullshort', 'langconfig'));
    }

    public function get_id() {
        return $this->id;
    }

    public function get_last_activity_dmy() {
        $date = $this->get_last_activity();
        if ($date === null) {
            return '-';
        }
        return userdate($this->get_last_activity(), get_string('strftimedatefullshort', 'langconfig'));
    }

    public function get_timecreated_dmy() {
        return userdate($this->timecreated, get_string('strftimedatefullshort', 'langconfig'));
    }

    public function get_answer_count() {
        global $DB;

        if (isset($this->answer_count)) {
            return $this->answer_count;
        }
        return $this->answer_count = $DB->count_records('lc_chat_comment', ['chatid' => $this->get_chat()->id, 'timedeleted' => null]);
    }
}
