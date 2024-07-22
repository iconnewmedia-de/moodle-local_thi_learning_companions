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
namespace local_thi_learning_companions;

use core\session\exception;
use local_thi_learning_companions\event\question_answered;
use local_thi_learning_companions\event\question_created;
use local_thi_learning_companions\traits\is_db_saveable;

/**
 * Question object representing a question and all related information
 */
class question {
    use is_db_saveable;

    /**
     * @var array
     */
    private static $topiclist = [];

    /**
     * @var int
     */
    public $id;
    /**
     * @var int
     */
    public $askedby;
    /**
     * @var int
     */
    public $mentorid;
    /**
     * @var string
     */
    public $question;
    /**
     * @var string
     */
    public $title;
    /**
     * @var string
     */
    public $topic;
    /**
     * @var int
     */
    public $timecreated;
    /**
     * @var int
     */
    public $timeclosed;
    /**
     * @var bool
     */
    public $mayuserdelete;

    /**
     * @var int
     */
    public $lastactive;
    /**
     * @var string
     */
    public $lastactivedmy;

    /**
     * returns the name of the database table
     * @return string
     */
    private static function get_table_name(): string {
        return 'local_thi_learning_companions_mentor_questions';
    }

    /**
     * Constructor
     * @param int $askedby
     * @param int $mentorid
     * @param string $question
     * @param string $title
     * @param string $topic
     */
    public function __construct(int $askedby, int $mentorid, string $question, string $title, string $topic) {
        $this->askedby = $askedby;
        $this->mentorid = $mentorid;
        $this->question = $question;
        $this->title = $title;
        $this->topic = $topic;
        $this->timecreated = time();
    }

    /**
     * creates a new open question to all mentors
     * @param string $question
     * @param string $title
     * @param string $topic
     * @return question
     * @throws \coding_exception
     */
    public static function ask_new_open_question(string $question, string $title, string $topic) {
        global $USER;

        $newquestion = new self($USER->id, 0, $question, $title, $topic);
        $newquestion->save();
        question_created::make($USER->id, $newquestion->id, $topic)->trigger();
        return $newquestion;
    }

    /**
     * creates a new question to a specific mentor
     * @param string $question
     * @param string $title
     * @param string $topic
     * @param int $mentorid
     * @return question
     * @throws \coding_exception
     */
    public static function ask_new_question(string $question, string $title, string $topic, int $mentorid) {
        global $USER;

        $newquestion = new self($USER->id, $mentorid, $question, $title, $topic);
        $newquestion->save();
        question_created::make($USER->id, $newquestion->id, $topic, $mentorid)->trigger();
        return $newquestion;
    }

    /**
     * creates an instance of this class from a given record
     * @param \stdClass $record
     * @return static
     * @throws \coding_exception
     * @throws \dml_exception
     */
    private static function from_record($record): self {
        if (is_null($record->mentorid)) {
            $record->mentorid = 0;
        }
        $question = new self($record->askedby, $record->mentorid, $record->question, $record->title, $record->topic);
        $question->id = $record->id;
        $question->timecreated = $record->timecreated;
        $question->timeclosed = $record->timeclosed;
        $question->mayuserdelete = mentors::may_user_delete_question($question->id);
        return $question;
    }

    /**
     * returns a question object for a question for which the user doesn't have permission to view it
     * @return static
     * @throws \coding_exception
     */
    private static function question_with_no_permission(): self {
        $question = new self(0, 0,
            get_string('no_permission_for_this_question', 'local_thi_learning_companions'),
            get_string('invalid_question_id', 'local_thi_learning_companions'), '');
        $question->id = 0;
        $question->timecreated = time();
        $question->timeclosed = time();
        return $question;
    }

    /**
     * marks a question as closed (accepted answer)
     * @return $this
     * @throws \coding_exception
     */
    public function mark_closed(): self {
        global $USER;
        $this->timeclosed = time();
        question_answered::make($USER->id, $this->id)->trigger();
        return $this;
    }

    /**
     * returns a question for a given question id
     * @param int $questionid
     * @return \local_thi_learning_companions\question
     * @throws \dml_exception
     * @throws \exception
     */
    public static function get_question_by_id($questionid) {
        global $DB, $USER;
        $record = $DB->get_record('local_thi_learning_companions_mentor_questions', ['id' => $questionid]);
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
     * returns all questions for a certain user
     * @param int $userid
     *
     * @return self[]
     * @throws \dml_exception
     */
    public static function get_all_questions_for_user(int $userid) {
        global $DB;

        $records = $DB->get_records('local_thi_learning_companions_mentor_questions', ['askedby' => $userid]);
        $questions = [];
        foreach ($records as $record) {
            $questions[] = self::find($record->id);
        }
        return $questions;
    }

    /**
     * returns true if the user may view the question of the current question object
     * @param int $userid
     * @return bool
     * @throws \dml_exception
     */
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
        $mentortopics = $DB->get_records_menu('local_thi_learning_companions_mentors', ['userid' => $userid], '', 'topic');
        $mentortopics = array_keys($mentortopics);
        return in_array($this->topic, $mentortopics, true);
    }

    /**
     * returns all questions that have been asked to a specific mentor
     * @param int $userid
     *
     * @return array
     * @throws \dml_exception
     */
    public static function get_all_questions_for_mentor_user(int $userid) {
        global $DB;

        $records = $DB->get_records('local_thi_learning_companions_mentor_questions', ['mentorid' => $userid]);
        $questions = [];
        foreach ($records as $record) {
            $questions[] = self::find($record->id);
        }
        return $questions;
    }

    /**
     * returns all questions for a certain topic
     * @param array $topics
     * @param bool $onlyopenquestions
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
        $records = $DB->get_records_sql('SELECT * FROM {local_thi_learning_companions_mentor_questions} WHERE topic '.$sql, $params);
        $questions = [];
        foreach ($records as $record) {
            $questions[] = self::find($record->id);
        }
        return $questions;
    }

    /**
     * returns date of last activity
     * @return false|int|mixed
     * @throws \dml_exception
     */
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
                'local_thi_learning_companions_chat_comment',
                'MAX(timecreated)',
                ['chatid' => $chat->id, 'timedeleted' => null]
            );
        }
        return $this->last_active;
    }

    /**
     * returns the chat for a question
     * @return false|mixed|\stdClass
     * @throws \dml_exception
     */
    private function get_chat() {
        global $DB;

        if (isset($this->chat)) {
            return $this->chat;
        }

        return $this->chat = $DB->get_record('local_thi_learning_companions_chat', ['relatedid' => $this->id, 'chattype' => groups::CHATTYPE_MENTOR]);
    }

    /**
     * returns the data of the current question object as an associative array
     * @return array
     */
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
     * returns the user who asked the question
     * @return int
     */
    public function get_askedby(): int {
        return $this->askedby;
    }

    /**
     * returns the closed status (closed if the user has marked it as such, usually because (s)he has accepted an answer
     * @return bool
     */
    public function is_closed() {
        return $this->timeclosed !== null && intval($this->timeclosed) !== 0;
    }

    /**
     * returns the timestamp of when the question was marked as closed
     * @return string
     * @throws \coding_exception
     */
    public function get_closed_time() {
        return userdate($this->timeclosed, get_string('strftimedatefullshort', 'langconfig'));
    }

    /**
     * returns the id of the question
     * @return mixed
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * returns the date of the last activity as string
     * @return string
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_last_activity_dmy() {
        $date = $this->get_last_activity();
        if ($date === null || $date == 0) {
            return '-';
        }
        return userdate($this->get_last_activity(), get_string('strftimedatefullshort', 'langconfig'));
    }

    /**
     * returns the creation date as string
     * @return string
     * @throws \coding_exception
     */
    public function get_timecreated_dmy() {
        return userdate($this->timecreated, get_string('strftimedatefullshort', 'langconfig'));
    }

    /**
     * returns the amount of answer for the current question
     * @return int
     * @throws \dml_exception
     */
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
                'local_thi_learning_companions_chat_comment',
                ['chatid' => $chat->id, 'timedeleted' => null]
            );
        }
        return $this->answer_count;
    }

    /**
     * returns the topic of the current question
     * @return false|mixed|string
     * @throws \dml_exception
     */
    public function get_topic() {
        if ($this->topic === '0') {
            return '-';
        }

        if (array_key_exists($this->topic, self::$topiclist)) {
            return self::$topiclist[$this->topic];
        }

        global $DB;

        $topic = $DB->get_field('local_thi_learning_companions_keywords', 'keyword', ['id' => $this->topic]) ?? '-';
        self::$topiclist[$this->topic] = $topic;
        return $topic;
    }
}
