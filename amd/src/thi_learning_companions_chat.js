/* eslint-disable no-console, no-unused-vars, max-len, jsdoc/require-jsdoc, no-inner-declarations */
import $ from 'jquery';
import * as str from 'core/str';
import * as ModalFactory from 'core/modal_factory';
import * as ModalEvents from 'core/modal_events';
import {
    handleGroupLeaveButton,
    handleGroupRequestButton,
    handleGroupInviteButton
} from 'local_thi_learning_companions/group';

let strings = [];

export const init = async() => {
    // Put send button to the right next to the message box
    // we can't arrange it that way with just Moodle Forms API
    const sendButton = $('#local_thi_learning_companions_chat-send');
    const messageBox = $('#id_messageeditable');
    messageBox.after(sendButton);
    // Add event listener to the send button to handle message sending
    sendButton.on('click', handleNewMessageSubmit);

    addBBBlinkButton();
    addUploadButton();

    const stringsObj = [
        {key: 'modal-deletecomment-title', component: 'local_thi_learning_companions'},
        {key: 'modal-deletecomment-text', component: 'local_thi_learning_companions'},
        {key: 'modal-deletecomment-okaybutton', component: 'local_thi_learning_companions'},
        {key: 'modal-editcomment-title', component: 'local_thi_learning_companions'},
        {key: 'modal-editcomment-okaybutton', component: 'local_thi_learning_companions'},
        {key: 'modal-reportcomment-title', component: 'local_thi_learning_companions'},
        {key: 'modal-reportcomment-text', component: 'local_thi_learning_companions'},
        {key: 'modal-reportcomment-okaybutton', component: 'local_thi_learning_companions'},
    ];

    // eslint-disable-next-line no-return-assign
    str.get_strings(stringsObj).then(results => strings = results);

    const body = $('body');

    body.on('click', '.thi_learning_companions_delete_comment', handleCommentDelete);
    body.on('click', '.thi_learning_companions_edit_comment', handleCommentEdit);
    body.on('click', '.thi_learning_companions_report_comment', handleCommentReport);
    body.on('click', '.thi_learning_companions_rate_comment', handleCommentRating);
    body.on('click', '.thi_learning_companions_editgroup', handleEditGroup);

    body.on('click', '.js-leave-group', handleGroupLeaveButton);
    body.on('click', '.js-request-join-group', handleGroupRequestButton);
    body.on('click', '.js-invite-member', handleGroupInviteButton);
    body.on('click', '.thi_learning_companions_bbb_button', handleBBBButton);
    body.on('click', '.thi_learning_companions_upload_button', handleUploadButton);
    let item = document.querySelector('#page-local-thi_learning_companions-chat #fitem_id_attachments');
    document.body.addEventListener('dragenter', function(e) {
        // console.log('started dragging', e);
        document.querySelector('#page-local-thi_learning_companions-chat #fitem_id_attachments').classList.add('upload-visible');
    });
    // document.body.addEventListener('dragstop', function(e) {
    //     console.log('stopped dragging', e);
    //     document.querySelector('#page-local-thi_learning_companions-chat #fitem_id_attachments').classList.remove('upload-visible');
    // });
};

const addBBBlinkButton = function() {
    var string = str.get_string(  'bigbluebutton_title', 'local_thi_learning_companions');
    string.then((title) => {
        if ($('.atto_editor_row').length > 0) {
            var appendTo = '#page-local-thi_learning_companions-chat .atto_toolbar_row:first-child';
        } else {
            var appendTo = '#page-local-thi_learning_companions-chat .editor_atto_toolbar';
        }
        $().add('<div class="atto_group accessibility_group"><button class="thi_learning_companions_bbb_button" title="' + title + '")>BigBlueButton</button></div>').appendTo(appendTo);
    });
};

const addUploadButton = function() {
    var string = str.get_string(  'upload_title', 'local_thi_learning_companions');
    string.then((title) => {
        if ($('.atto_editor_row').length > 0) {
            var appendTo = '#page-local-thi_learning_companions-chat .atto_toolbar_row:first-child';
        } else {
            var appendTo = '#page-local-thi_learning_companions-chat .editor_atto_toolbar';
        }
        $().add('<div class="atto_group accessibility_group"><button class="thi_learning_companions_upload_button" title="' + title + '"><i class="fa fa-upload"></i></button></div>').appendTo(appendTo);
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
        let child = Math.max(childElementCount - 1, 0);
        range.setStartAfter(messageEditor.children[child]);
        document.getSelection().addRange(range);
    }
    var string = str.get_string('bigbluebutton_join_text', 'local_thi_learning_companions');
    var chatid = $('.chat-post-form input[name="chatid"]').val();
    var bbbURLs = $.ajax(M.cfg.wwwroot + '/local/thi_learning_companions/ajax/ajax_videocall.php', {data: {chatid:chatid}});
    Promise.all([string,bbbURLs]).then((data) => {
        const urls = data[1];
        window.open(urls['moderator'], '_blank');
        pasteHtmlAtCaret(' <a target="_blank" href="' + urls['participants'] + '">' + data[0] + '</a>');

    });
    // ICTODO: Create a new BBB room, then create a link that the users can use to join the room, all via AJAX
    return false;
};

const handleUploadButton = function(e){
    e.preventDefault();
    const uploadfield = document.querySelector('#page-local-thi_learning_companions-chat #fitem_id_attachments');
    let target = e.target;
    if (target.tagName == "I") {
        target = target.parentElement;
    }
    if (uploadfield.classList.contains('upload-visible')) {
        uploadfield.classList.remove('upload-visible');
        target.classList.remove('highlight');
    } else {
        uploadfield.classList.add('upload-visible');
        target.classList.add('highlight');
    }
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
const handleCommentRating = function(e) {
    const postId = +e.target.dataset.id;
    $.ajax({
        url: M.cfg.wwwroot + '/local/thi_learning_companions/ajax/ajax_ratecomment.php',
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
            document.dispatchEvent(new CustomEvent('thi_learning_companions_message_rated', {detail: {postid: postId, newvalue: data.israted}}));
            // document.dispatchEvent(new ModalEvents.hidden);
        }
    });
};
const handleCommentDelete = async function(e) {
    const postId = +e.target.dataset.id;

    const modal = await ModalFactory.create({
        type: ModalFactory.types.SAVE_CANCEL,
        title: strings[0],
        body: '' +
            '<div id="thi_learning_companions-deletecomment-modal">' +
            '<div id="thi_learning_companions-deletecomment-modal-text">' + strings[1] + '</div>' +
            '</div>',
    });

    // console.log('comment id from data:', postId);

    modal.getRoot().on(ModalEvents.save, function() {
        $.ajax({
            url: M.cfg.wwwroot + '/local/thi_learning_companions/ajax/ajaxdeletecomment.php',
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
                document.dispatchEvent(new CustomEvent('thi_learning_companions_message_deleted', {detail: {postid: postId}}));
                // document.dispatchEvent(new ModalEvents.hidden);
            }
        });
    });
    modal.setSaveButtonText(strings[2]);
    modal.show();
};
const handleCommentEdit = async function(e) {
    // console.log('clicked edit comment. event object:', e);
    // console.log('clicked edit comment for comment id:', e.target.dataset.id);
    var commentid = e.target.dataset.id;
    // console.log('commentid before creating modal:', commentid);
    // console.log(ModalFactory);
    return ModalFactory.create({
        type: ModalFactory.types.SAVE_CANCEL,
        title: strings[3],
        body: '' +
            '<div id="thi_learning_companions-edit-modal">' +
            '<div id="thi_learning_companions-edit-modal-text">' + strings[4] + '</div>' +
            '</div>',
        // footer: '' +
        //     '<div id="thi_learning_companions-deletecomment-modal-buttons">' +
        //     '<button class="btn btn-primary" aria-hidden="true" id="thi_learning_companions-deletecomment-modal-delete" data-cid="' + commentid + '">' + strings[2] + '</button>' +
        //     '<button class="btn btn-secondary" aria-hidden="true" id="thi_learning_companions-deletecomment-modal-close" data-action="hide">' + strings[3] + '</button>' +
        //     '</div>'
    }).then(function(modal) {
        // console.log('edit comment id from data:', commentid);

        modal.setSaveButtonText(strings[5]);
        $.ajax(
            {
                url: M.cfg.wwwroot + '/local/thi_learning_companions/editcomment.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    commentid: commentid
                }
            }
        ).done(function(a, b, c) {
            // console.log('inside edit comment. a:', a, 'b:', b, 'c:', c);
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
            '<div id="thi_learning_companions-reportcomment-modal">' +
            '<div id="thi_learning_companions-reportcomment-modal-text">' + strings[6] + '</div>' +
            '</div>'
    }).then(function(modal) {
        // console.log('comment id from data:', postId);

        modal.getRoot().on(ModalEvents.save, function() {
            // console.log('about to call ajaxdeletecomment.php with comment id', postId);
            $.ajax({
                url: `${M.cfg.wwwroot}/local/thi_learning_companions/ajax/ajaxreport.php`,
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
                    document.dispatchEvent(new CustomEvent('thi_learning_companions_message_reported', {detail: {postid: postId}}));
                    // document.dispatchEvent(ModalEvents.hidden);
                }
            });
        });
        modal.setSaveButtonText(strings[7]);
        modal.show();
    });
};
const handleEditGroup = async function(e) {
    // console.log('clicked on gear icon', this, e);

    async function callGroupModal(e) {
        e.preventDefault();

        const groupid = e.target.dataset.gid;
        const groupname = e.target.dataset.title;
        // console.log('getting group modal for ', groupid, groupname, this, e);
        const groupDetails = $.ajax({
            url: M.cfg.wwwroot + '/local/thi_learning_companions/ajax/ajax.php',
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'getgroupdetails',
                groupid: groupid,
                referrer: 'chat'
            },
            success: function(data) {
                console.log('got group details modal with data: ', data);
                const title = str.get_string('modal-groupdetails-groupname', 'local_thi_learning_companions', groupname);
                title.then(function(string) {
                    // console.log('got group title string:', title);
                    // console.log('got group details data:', data);
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
    $('#thi_learning_companions_chat form input, #thi_learning_companions_chat form textarea').each((index, el) => {
        data[el.name] = el.value;
    });
    $('#thi_learning_companions_chat #id_messageeditable').css('opacity', '0.5');
    $('#thi_learning_companions_chat #id_messageeditable').attr('contenteditable', 'false');
    $('#thi_learning_companions_chat form input,  #thi_learning_companions_chat form textarea, #local_thi_learning_companions_chat-send').attr('disabled', true);
    $.post(
        M.cfg.wwwroot + "/local/thi_learning_companions/ajax/ajaxsubmit.php",
        data
    ).done(function(a, b, c) {
        // ICTODO: give a success message, like with a toast or so
        // reset the form to empty values after successfully sending the form
        if (a.warning_body) {
            console.log('output warning');
            ModalFactory.create({
                title: a.warning_title,
                body: a.warning_body,
                footer: '',
                large: false,
                type: ModalFactory.types.ALERT,
            }).then(modal => {
                modal.show();
            });
        }
        $('#thi_learning_companions_chat form #id_messageeditable').text("");
        $('#thi_learning_companions_chat form input, #thi_learning_companions_chat form textarea').each((index, el) => {
            if (el.name == "textarea" || el.type !== "hidden") {
                el.value = '';
            }
        });

        //Update the itemid, because otherwise, if the user tries to upload a file with the same name as the previous one,
        //it will fail.
        $('input[type=hidden][name=message\\[itemid\\]]').val(a.itemid);
        //We also need to update the itemid inside the template options. Getting the first clientId, which is random, and then
        //updating the itemid in the options.
        const clientId = Object.keys(window.M.core_filepicker.instances).pop();
        if (clientId) {
            window.M.core_filepicker.instances[clientId].options.itemid = a.itemid;
        }

        document.dispatchEvent(new CustomEvent('thi_learning_companions_message_send'));
    }).fail(function(a, b, c) {
        console.warn('Failed sending via AJAX', a, b, c);
        window.alert("couldn't save"); // ICTODO: give proper message, via get_string and ideally with a modal
    }).always(function(a, b, c) {
        // reactivate the form/ungrey it when data has been sent
        $('#thi_learning_companions_chat #id_messageeditable').css('opacity', '1');
        $('#thi_learning_companions_chat #id_messageeditable').attr('contenteditable', 'true');
        $('#thi_learning_companions_chat form input,  #thi_learning_companions_chat form textarea, #local_thi_learning_companions_chat-send').attr('disabled', false);
    });
    return false;
};
