<?php
defined('MOODLE_INTERNAL') || die();
require_once $CFG->dirroot . '/lib/form/editor.php';
MoodleQuickForm::registerElementType('lccustomeditor', __FILE__, 'MoodleQuickForm_lccustomeditor');

/**
 * like MoodleQuickForm_editor but allows to specify which buttons to use by passsing a value for 'atto:toolbar' in the options array
 */
class MoodleQuickForm_lccustomeditor extends MoodleQuickForm_editor {
    /** @var array options provided to initalize filepicker */
    protected $_options = array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 0, 'changeformat' => 0,
        'areamaxbytes' => FILE_AREA_MAX_BYTES_UNLIMITED, 'context' => null, 'noclean' => 0, 'trusttext' => 0,
        'return_types' => 15, 'enable_filemanagement' => true, 'removeorphaneddrafts' => false, 'autosave' => true,
        'atto:toolbar' => 'collapse = collapse
style1 = title, bold, italic
list = unorderedlist, orderedlist, indent
links = link
files = emojipicker, image, media, recordrtc, managefiles, h5p
accessibility = accessibilitychecker, accessibilityhelper
style2 = underline, strike, subscript, superscript
align = align
insert = equation, charmap, table, clear
undo = undo
other = html');

}