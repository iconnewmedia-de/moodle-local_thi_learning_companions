/* eslint-disable no-console, no-unused-vars, max-len, jsdoc/require-jsdoc, no-inner-declarations */
import $ from 'jquery';
import * as str from 'core/str';
import * as ModalFactory from 'core/modal_factory';
import * as ModalEvents from 'core/modal_events';
import {
    handleGroupLeaveButton,
    handleGroupRequestButton,
    handleGroupInviteButton
} from 'local_learningcompanions/group';

let strings = [];

export const init = async() => {
    // Put send button to the right next to the message box
    // we can't arrange it that way with just Moodle Forms API
    const sendButton = $('#local_learningcompanions_chat-send');
    const messageBox = $('#id_messageeditable');
    messageBox.after(sendButton);
    // Add event listener to the send button to handle message sending
    sendButton.on('click', handleNewMessageSubmit);

    addBBBlinkButton();

    const stringsObj = [
        {key: 'modal-deletecomment-title', component: 'local_learningcompanions'},
        {key: 'modal-deletecomment-text', component: 'local_learningcompanions'},
        {key: 'modal-deletecomment-okaybutton', component: 'local_learningcompanions'},
        {key: 'modal-editcomment-title', component: 'local_learningcompanions'},
        {key: 'modal-editcomment-okaybutton', component: 'local_learningcompanions'},
        {key: 'modal-reportcomment-title', component: 'local_learningcompanions'},
        {key: 'modal-reportcomment-text', component: 'local_learningcompanions'},
        {key: 'modal-reportcomment-okaybutton', component: 'local_learningcompanions'},
    ];

    // eslint-disable-next-line no-return-assign
    str.get_strings(stringsObj).then(results => strings = results);

    const body = $('body');

    body.on('click', '.learningcompanions_delete_comment', handleCommentDelete);
    body.on('click', '.learningcompanions_edit_comment', handleCommentEdit);
    body.on('click', '.learningcompanions_report_comment', handleCommentReport);
    body.on('click', '.learningcompanions_editgroup', handleEditGroup);

    body.on('click', '.js-leave-group', handleGroupLeaveButton);
    body.on('click', '.js-request-join-group', handleGroupRequestButton);
    body.on('click', '.js-invite-member', handleGroupInviteButton);
    body.on('click', '.learningcompanions_bbb_button', handleBBBButton);
};

const addBBBlinkButton = function() {
    var string = str.get_string(  'bigbluebutton_title', 'local_learningcompanions');
    string.then((title) => {
        $().add('<div class="atto_group accessibility_group"><button class="learningcompanions_bbb_button" title="' + title + '")>BigBlueButton</button></div>').appendTo('.atto_toolbar_row:first-child');
    });
};
const handleBBBButton = function(e){
    e.preventDefault();
    var sel = window.getSelection();
    let messageEditorSelected = false;
    const messageEditor = document.getElementById('id_messageeditable');
    // eslint-disable-next-line no-cond-assign
    while (sel = sel.parentNode){
        if (sel.id === 'id_messageeditable') {
            messageEditorSelected = true;
            break;
        }
    }
    if (!messageEditorSelected) {
        document.getSelection().removeAllRanges();
        messageEditor.focus();
        const range = new Range();
        const childElementCount = messageEditor.childElementCount;
        range.setStartAfter(messageEditor.children[childElementCount - 1]);
        document.getSelection().addRange(range);
    }
    var string = str.get_string('bigbluebutton_join_text', 'local_learningcompanions');
    var bbbURLs = $.ajax(M.cfg.wwwroot + '/local/learningcompanions/ajax_videocall.php');
    Promise.all([string,bbbURLs]).then((data) => {
        const urls = data[1];
        window.open(urls['moderator'], '_blank');
        pasteHtmlAtCaret(' <a target="_blank" href="' + urls['participants'] + '">' + data[0] + '</a>');

    });
    // ICTODO: Create a new BBB room, then create a link that the users can use to join the room, all via AJAX
    return false;
};
const pasteHtmlAtCaret = function(html) {
    var sel, range;
    if (window.getSelection) {
        // IE9 and non-IE
        sel = window.getSelection();
        if (sel.getRangeAt && sel.rangeCount) {
            range = sel.getRangeAt(0);
            range.deleteContents();

            // Range.createContextualFragment() would be useful here but is
            // only relatively recently standardized and is not supported in
            // some browsers (IE9, for one)
            var el = document.createElement("div");
            el.innerHTML = html;
            var frag = document.createDocumentFragment(), node, lastNode;
            while ( (node = el.firstChild) ) {
                lastNode = frag.appendChild(node);
            }
            range.insertNode(frag);

            // Preserve the selection
            if (lastNode) {
                range = range.cloneRange();
                range.setStartAfter(lastNode);
                range.collapse(true);
                sel.removeAllRanges();
                sel.addRange(range);
            }
        }
    } else if (document.selection && document.selection.type != "Control") {
        // IE < 9
        document.selection.createRange().pasteHTML(html);
    }
};
const handleCommentDelete = async function(e) {
    const postId = +e.target.dataset.id;

    const modal = await ModalFactory.create({
        type: ModalFactory.types.SAVE_CANCEL,
        title: strings[0],
        body: '' +
            '<div id="learningcompanions-deletecomment-modal">' +
            '<div id="learningcompanions-deletecomment-modal-text">' + strings[1] + '</div>' +
            '</div>',
    });

    console.log('comment id from data:', postId);

    modal.getRoot().on(ModalEvents.save, function() {
        $.ajax({
            url: M.cfg.wwwroot + '/local/learningcompanions/ajax/ajaxdeletecomment.php',
            method: 'POST',
            dataType: 'json',
            data: {
                commentid: postId
            },
            success: function(data) {
                // modal.destroy();
                if (data === 'fail') {
                    // ICTODO: output fail message
                } else {
                    // ICTODO: output success message
                }
                document.dispatchEvent(new CustomEvent('learningcompanions_message_deleted', {detail: {postid: postId}}));
                // document.dispatchEvent(new ModalEvents.hidden);
            }
        });
    });
    modal.setSaveButtonText(strings[2]);
    modal.show();
};
const handleCommentEdit = async function(e) {
    console.log('clicked edit comment. event object:', e);
    console.log('clicked edit comment for comment id:', e.target.dataset.id);
    var commentid = e.target.dataset.id;
    console.log('commentid before creating modal:', commentid);
    console.log(ModalFactory);
    return ModalFactory.create({
        type: ModalFactory.types.SAVE_CANCEL,
        title: strings[3],
        body: '' +
            '<div id="learningcompanions-edit-modal">' +
            '<div id="learningcompanions-edit-modal-text">' + strings[4] + '</div>' +
            '</div>',
        // footer: '' +
        //     '<div id="learningcompanions-deletecomment-modal-buttons">' +
        //     '<button class="btn btn-primary" aria-hidden="true" id="learningcompanions-deletecomment-modal-delete" data-cid="' + commentid + '">' + strings[2] + '</button>' +
        //     '<button class="btn btn-secondary" aria-hidden="true" id="learningcompanions-deletecomment-modal-close" data-action="hide">' + strings[3] + '</button>' +
        //     '</div>'
    }).then(function(modal) {
        console.log('edit comment id from data:', commentid);

        modal.setSaveButtonText(strings[5]);
        $.ajax(
            {
                url: M.cfg.wwwroot + '/local/learningcompanions/editcomment.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    commentid: commentid
                }
            }
        ).done(function(a, b, c) {
            console.log('inside edit comment. a:', a, 'b:', b, 'c:', c);
            modal.setText(a.text);
        });
        // modal.getRoot().on(ModalEvents.hidden, function () {
        //     modal.destroy();
        // });
        modal.show();
    });
};
const handleCommentReport = async function(e) {
    const postId = +e.target.dataset.id;

    return ModalFactory.create({
        type: ModalFactory.types.SAVE_CANCEL,
        title: strings[5],
        body: '' +
            '<div id="learningcompanions-reportcomment-modal">' +
            '<div id="learningcompanions-reportcomment-modal-text">' + strings[6] + '</div>' +
            '</div>'
    }).then(function(modal) {
        console.log('comment id from data:', postId);

        modal.getRoot().on(ModalEvents.save, function() {
            console.log('about to call ajaxdeletecomment.php with comment id', postId);
            $.ajax({
                url: `${M.cfg.wwwroot}/local/learningcompanions/ajax/ajaxreport.php`,
                method: 'POST',
                dataType: 'json',
                data: {
                    commentid: postId
                },
                success: function(data) {
                    // modal.destroy();
                    if (data === 'fail') {
                        // ICTODO: output fail message
                    } else {
                        // ICTODO: output success message
                    }
                    document.dispatchEvent(new CustomEvent('learningcompanions_message_reported', {detail: {postid: postId}}));
                    // document.dispatchEvent(ModalEvents.hidden);
                }
            });
        });
        modal.setSaveButtonText(strings[7]);
        modal.show();
    });
};
const handleEditGroup = async function(e) {
    console.log('clicked on gear icon', this, e);

    async function callGroupModal(e) {
        e.preventDefault();

        const groupid = e.target.dataset.gid;
        const groupname = e.target.dataset.title;
        console.log('getting group modal for ', groupid, groupname, this, e);
        const groupDetails = $.ajax({
            url: M.cfg.wwwroot + '/local/learningcompanions/ajax/ajax.php',
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'getgroupdetails',
                groupid: groupid
            },
            success: function(data) {
                const title = str.get_string('modal-groupdetails-groupname', 'local_learningcompanions', groupname);
                title.then(function(string) {
                    console.log('got group title string:', title);
                    console.log('got group details data:', data);
                    ModalFactory.create({
                        title: string,
                        body: data.html,
                        footer: '',
                        large: true,
                        type: ModalFactory.types.CANCEL,
                    }).then(modal => {
                        modal.show();
                    });
                });
            }
        });
    }

    callGroupModal(e).then(() => {
        console.log('called modal for group details. What now?');
    });
};

const handleNewMessageSubmit = (e) => {
    if (e.target.getAttribute("disabled") === true) {
        return;
    }
    // ICTODO: disable the form/grey it out, while data is getting sent
    e.preventDefault();
    const data = {};
    $('#learningcompanions_chat form input, #learningcompanions_chat form textarea').each((index, el) => {
        data[el.name] = el.value;
    });
    $('#learningcompanions_chat #id_messageeditable').css('opacity', '0.5');
    $('#learningcompanions_chat #id_messageeditable').attr('contenteditable', 'false');
    $('#learningcompanions_chat form input,  #learningcompanions_chat form textarea, #local_learningcompanions_chat-send').attr('disabled', true);
    $.post(
        M.cfg.wwwroot + "/local/learningcompanions/ajax/ajaxsubmit.php",
        data
    ).done(function(a, b, c) {
        // ICTODO: give a success message, like with a toast or so
        // reset the form to empty values after successfully sending the form
        $('#learningcompanions_chat form #id_messageeditable').text("");
        $('#learningcompanions_chat form input, #learningcompanions_chat form textarea').each((index, el) => {
            if (el.name == "textarea" || el.type !== "hidden") {
                el.value = '';
            }
        });
    }).fail(function(a, b, c) {
        console.warn('Failed sending via AJAX', a, b, c);
        window.alert("couldn't save"); // ICTODO: give proper message, via get_string and ideally with a modal
    }).always(function() {
        // reactivate the form/ungrey it when data has been sent
        $('#learningcompanions_chat #id_messageeditable').css('opacity', '1');
        $('#learningcompanions_chat #id_messageeditable').attr('contenteditable', 'true');
        $('#learningcompanions_chat form input,  #learningcompanions_chat form textarea, #local_learningcompanions_chat-send').attr('disabled', false);
    });
    return false;
};
