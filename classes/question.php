<?php

namespace local_learningcompanions;

use core\session\exception;
use local_learningcompanions\event\question_answered;
use local_learningcompanions\event\question_created;
use local_learningcompanions\traits\is_db_saveable;

class question {
    use is_db_saveable;

    private static $topic_list = [];

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
        question_created::make($USER->id, $new_question->id, $topic)->trigger();
        return $new_question;
    }

    public static function ask_new_question(string $question, string $title, int $topic, int $mentorid) {
        global $USER;

        $new_question = new self($USER->id, $mentorid, $question, $title, $topic);
        $new_question->save();
        question_created::make($USER->id, $new_question->id, $topic, $mentorid)->trigger();
        return $new_question;
    }

    private static function from_record($record): self {
        if (is_null($record->mentorid)) {
            $record->mentorid = 0;
        }
        $question = new self($record->askedby, $record->mentorid, $record->question, $record->title, $record->topic);
        $question->id = $record->id;
        $question->timecreated = $record->timecreated;
        $question->timeclosed = $record->timeclosed;

        return $question;
    }
    private static function question_with_no_permission($record): self {
        $question = new self(0, 0,
            get_string('no_permission_for_this_question', 'local_learningcompanions'),
            get_string('invalid_question_id', 'local_learningcompanions'), '');
        $question->id = 0;
        $question->timecreated = time();
        $question->timeclosed = time();
        return $question;
    }

    public function mark_closed(): self {
        global $USER;
        $this->timeclosed = time();
        question_answered::make($USER->id, $this->id)->trigger();
        return $this;
    }

    /**
     * @param $questionid
     * @return \local_learningcompanions\question
     * @throws \dml_exception
     * @throws \exception
     */
    public static function get_question_by_id($questionid) {
        global $DB, $USER;
        $record = $DB->get_record('lc_mentor_questions', array('id' => $questionid));
        if (!$record) {
            throw new \exception('invalid_question_id', 'local_learningcompanions');
        }
        $isMentor = \local_learningcompanions\mentors::is_mentor();
        $context = \context_system::instance();
        if (($isMentor && $record->mentorid == 0)
            || $USER->id == $record->askedby
            || $USER->id == $record->mentorid
            || has_capability('local/learningcompanions:view_all_mentor_questions', $context)
        ) {
            return self::from_record($record);
        } else {
            return self::question_with_no_permission();
        }
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

    public function can_user_view(int $userId): bool {
        global $DB;

        //If it´s the user who asked the question, they can view it.
        if ($this->askedby === $userId) {
            return true;
        }

        //If its the user who is the mentor, they can view it.
        if ($this->mentorid === $userId) {
            return true;
        }

        //If a mentor is assigned, and the user does not match from the previous checks, they cannot view it.
        if ($this->mentorid) {
            return false;
        }

        //There is no mentor assigned, so we need to check the topics
        $mentorTopics = $DB->get_records_menu('lc_mentors', ['userid' => $userId], '', 'topic');
        return in_array($this->topic, $mentorTopics, true);
    }

    /**
     * @param int $userid
     *
     * @return array
     * @throws \dml_exception
     */
    public static function get_all_questions_for_mentor_user(int $userid) {
        global $DB;

        $records = $DB->get_records('lc_mentor_questions', ['mentorid' => $userid]);
        $questions = [];
        foreach ($records as $record) {
            $questions[] = self::find($record->id);
        }
        return $questions;
    }

    public static function get_all_questions_by_topics(array $topics) {
        global $DB;

        [$sql, $params] = $DB->get_in_or_equal($topics);

        $records = $DB->get_records_sql('SELECT * FROM {lc_mentor_questions} WHERE topic '.$sql, $params);
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

    public function get_topic() {
        if ($this->topic === '0') {
            return '-';
        }

        if (array_key_exists($this->topic, self::$topic_list)) {
            return self::$topic_list[$this->topic];
        }

        global $DB;

        $topic =  $DB->get_field('lc_keywords', 'keyword', ['id' => $this->topic]) ?? '-';
        self::$topic_list[$this->topic] = $topic;
        return $topic;
    }
}
