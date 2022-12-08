<?php
namespace local_learningcompanions;
defined('MOODLE_INTERNAL') || die();
class comment {
    public $id;
    public $chatid;
    public $userid;
    public $comment;
    public $flagged;
    public $flaggedby;
    public $totalscore;
    public $timecreated;
    public $timemodified;
    public function __construct($id) {
        global $DB;
        $this->id = $id;
        $comment = $DB->get_record('lc_chat_comment', array('id' => $id));
        foreach($comment as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        $this->attachments = local_learningcompanions_get_attachments_of_chat_comments([$comment], 'attachments');
//        foreach($this->attachments as $key => $attachment) {
//            if (empty($attachment)) {
//                continue;
//            }
//            $url = \moodle_url::make_pluginfile_url($attachment->get_contextid(), $attachment->get_component(), $attachment->get_filearea(), $attachment->get_itemid(), $attachment->get_filepath(), $attachment->get_filename(), false);
//        }
    }
}