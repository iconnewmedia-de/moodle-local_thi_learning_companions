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

use core\session\exception;
use local_thi_learning_companions\event\question_answered;
use local_thi_learning_companions\event\question_created;
use local_thi_learning_companions\traits\is_db_saveable;

class question {
    use is_db_saveable;

    private static $topiclist = [];

    public $id;
    public $askedby;
    public $mentorid;
    public $question;
    public $title;
    public $topic;
    public $timecreated;
    public $timeclosed;

    public $lastactive;
    public $lastactivedmy;

    private static function get_table_name(): string {
        return 'thi_lc_mentor_questions';
    }

    public function __construct(int $askedby, int $mentorid, string $question, string $title, string $topic) {
        $this->askedby = $askedby;
        $this->mentorid = $mentorid;
        $this->question = $question;
        $this->title = $title;
        $this->topic = $topic;
        $this->timecreated = time();
    }

    public static function ask_new_open_question(string $question, string $title, string $topic) {
        global $USER;

        $newquestion = new self($USER->id, 0, $question, $title, $topic);
        $newquestion->save();
        question_created::make($USER->id, $newquestion->id, $topic)->trigger();
        return $newquestion;
    }

    public static function ask_new_question(string $question, string $title, string $topic, int $mentorid) {
        global $USER;

        $newquestion = new self($USER->id, $mentorid, $question, $title, $topic);
        $newquestion->save();
        question_created::make($USER->id, $newquestion->id, $topic, $mentorid)->trigger();
        return $newquestion;
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
    private static function question_with_no_permission(): self {
        $question = new self(0, 0,
            get_string('no_permission_for_this_question', 'local_thi_learning_companions'),
            get_string('invalid_question_id', 'local_thi_learning_companions'), '');
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
     * @return \local_thi_learning_companions\question
     * @throws \dml_exception
     * @throws \exception
     */
    public static function get_question_by_id($questionid) {
        global $DB, $USER;
        $record = $DB->get_record('thi_lc_mentor_questions', ['id' => $questionid]);
        if (!$record) {
            throw new \exception('invalid_question_id', 'local_thi_learning_companions');
        }
        $ismentor = \local_thi_learning_companions\mentors::is_mentor();
        $context = \context_system::instance();
        if (($ismentor && $record->mentorid == 0)
            || $USER->id == $record->askedby
            || $USER->id == $record->mentorid
            || has_capability('local/thi_learning_companions:view_all_mentor_questions', $context)
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

        $records = $DB->get_records('thi_lc_mentor_questions', ['askedby' => $userid]);
        $questions = [];
        foreach ($records as $record) {
            $questions[] = self::find($record->id);
        }
        return $questions;
    }

    public function can_user_view(int $userid): bool {
        global $DB;

        // If it´s the user who asked the question, they can view it.
        if ($this->askedby === $userid) {
            return true;
        }

        // If its the user who is the mentor, they can view it.
        if ($this->mentorid === $userid) {
            return true;
        }

        // If a mentor is assigned, and the user does not match from the previous checks, they cannot view it.
        if ($this->mentorid) {
            return false;
        }

        // There is no mentor assigned, so we need to check the topics.
        $mentortopics = $DB->get_records_menu('thi_lc_mentors', ['userid' => $userid], '', 'topic');
        return in_array($this->topic, $mentortopics, true);
    }

    /**
     * @param int $userid
     *
     * @return array
     * @throws \dml_exception
     */
    public static function get_all_questions_for_mentor_user(int $userid) {
        global $DB;

        $records = $DB->get_records('thi_lc_mentor_questions', ['mentorid' => $userid]);
        $questions = [];
        foreach ($records as $record) {
            $questions[] = self::find($record->id);
        }
        return $questions;
    }

    /**
     * @param array $topics
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function get_all_questions_by_topics(array $topics, bool $onlyopenquestions = false) {
        global $DB;
        if (empty($topics)) {
            return [];
        }
        [$sql, $params] = $DB->get_in_or_equal($topics);
        if ($onlyopenquestions) {
            $sql .= ' AND mentorid = 0';
        }
        $records = $DB->get_records_sql('SELECT * FROM {thi_lc_mentor_questions} WHERE topic '.$sql, $params);
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
        if (!$chat) {
            $this->last_active = 0;
        } else {
            $this->last_active = $DB->get_field(
                'thi_lc_chat_comment',
                'MAX(timecreated)',
                ['chatid' => $chat->id, 'timedeleted' => null]
            );
        }
        return $this->last_active;
    }

    private function get_chat() {
        global $DB;

        if (isset($this->chat)) {
            return $this->chat;
        }

        return $this->chat = $DB->get_record('thi_lc_chat', ['relatedid' => $this->id, 'chattype' => groups::CHATTYPE_MENTOR]);
    }

    public function to_array() {
        return [
            'id' => $this->id,
            'askedby' => $this->askedby,
            'mentorid' => $this->mentorid,
            'question' => $this->question,
            'title' => $this->title,
            'topic' => $this->topic,
            'timecreated' => $this->timecreated,
            'timeclosed' => $this->timeclosed,
        ];
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
        if ($date === null || $date == 0) {
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
        $chat = $this->get_chat();
        if (!$chat) {
            $this->answer_count = 0;
        } else {
            $this->answer_count = $DB->count_records(
                'thi_lc_chat_comment',
                ['chatid' => $chat->id, 'timedeleted' => null]
            );
        }
        return $this->answer_count;
    }

    public function get_topic() {
        if ($this->topic === '0') {
            return '-';
        }

        if (array_key_exists($this->topic, self::$topiclist)) {
            return self::$topiclist[$this->topic];
        }

        global $DB;

        $topic = $DB->get_field('thi_lc_keywords', 'keyword', ['id' => $this->topic]) ?? '-';
        self::$topiclist[$this->topic] = $topic;
        return $topic;
    }
}
