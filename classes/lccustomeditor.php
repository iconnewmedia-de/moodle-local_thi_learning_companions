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

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/lib/form/editor.php');
MoodleQuickForm::registerElementType(
    'lccustomeditor',
    $CFG->dirroot . '/local/thi_learning_companions/classes/' . __FILE__,
    'MoodleQuickForm_lccustomeditor'
);

/**
 * like MoodleQuickForm_editor but allows to specify which buttons to use
 * by passsing a value for 'atto:toolbar' in the options array
 */
class MoodleQuickForm_lccustomeditor extends MoodleQuickForm_editor {
    /** @var array options provided to initalize filepicker */
    protected $_options = ['subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 0, 'changeformat' => 0,
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
other = html'];

}
