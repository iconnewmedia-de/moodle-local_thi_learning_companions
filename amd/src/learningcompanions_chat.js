/* eslint-disable no-console, no-unused-vars, max-len, jsdoc/require-jsdoc, no-inner-declarations */
import $ from 'jquery';
import * as str from 'core/str';
import * as ModalFactory from 'core/modal_factory';
import * as ModalEvents from 'core/modal_events';

let strings = [];

export const init = async() => {
    // Put send button to the right next to the message box
    // we can't arrange it that way with just Moodle Forms API
    const sendButton = $('#local_learningcompanions_chat-send');
    const messageBox = $('#id_messageeditable');
    messageBox.after(sendButton);
    // Add event listener to the send button to handle message sending
    sendButton.on('click', handleNewMessageSubmit);

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

    strings = await str.get_strings(stringsObj);
    console.log('Strings:', strings);

    const body = $('body');

    body.on('click', '.learningcompanions_delete_comment', handleCommentDelete);
    body.on('click', '.learningcompanions_edit_comment', handleCommentEdit);
    body.on('click', '.learningcompanions_report_comment', handleCommentReport);
    body.on('click', '.learningcompanions_editgroup', handleEditGroup);
};

const handleCommentDelete = async function(e) {
    console.log('clicked delete comment. event object:', e);
    console.log('clicked delete comment for comment id:', e.target.dataset.id);
    var commentid = e.target.dataset.id;
    console.log('commentid before creating modal:', commentid);
    console.log(ModalFactory);

    return ModalFactory.create({
        type: ModalFactory.types.SAVE_CANCEL,
        title: strings[0],
        body: '' +
            '<div id="learningcompanions-deletecomment-modal">' +
            '<div id="learningcompanions-deletecomment-modal-text">' + strings[1] + '</div>' +
            '</div>',
        // footer: '' +
        //     '<div id="learningcompanions-deletecomment-modal-buttons">' +
        //     '<button class="btn btn-primary" aria-hidden="true" id="learningcompanions-deletecomment-modal-delete" data-cid="' + commentid + '">' + strings[2] + '</button>' +
        //     '<button class="btn btn-secondary" aria-hidden="true" id="learningcompanions-deletecomment-modal-close" data-action="hide">' + strings[3] + '</button>' +
        //     '</div>'
    }).then(function(modal) {
        console.log('comment id from data:', commentid);

        modal.getRoot().on(ModalEvents.save, function() {
            console.log('about to call ajaxdeletecomment.php with comment id', commentid);
            $.ajax({
                url: M.cfg.wwwroot + '/local/learningcompanions/ajaxdeletecomment.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    commentid: commentid
                },
                success: function(data) {
                    // modal.destroy();
                    if (data === 'fail') {
                        // ICTODO: output fail message
                    } else {
                        // ICTODO: output success message
                    }
                    document.dispatchEvent(new CustomEvent('learningcompanions_message_deleted', {detail: {postid: commentid}}));
                    document.dispatchEvent(new ModalEvents.hidden);
                }
            });
        });
        modal.setSaveButtonText(strings[2]);
        // modal.getRoot().on(ModalEvents.hidden, function () {
        //     modal.destroy();
        // });
        modal.show();
    });
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
    console.log('clicked report comment. event object:', e);
    console.log('clicked report comment for comment id:', e.target.dataset.id);
    var commentid = e.target.dataset.id;
    console.log('commentid before creating modal:', commentid);
    console.log(ModalFactory);
    return ModalFactory.create({
        type: ModalFactory.types.SAVE_CANCEL,
        title: strings[5],
        body: '' +
            '<div id="learningcompanions-reportcomment-modal">' +
            '<div id="learningcompanions-reportcomment-modal-text">' + strings[6] + '</div>' +
            '</div>',
        // footer: '' +
        //     '<div id="learningcompanions-deletecomment-modal-buttons">' +
        //     '<button class="btn btn-primary" aria-hidden="true" id="learningcompanions-deletecomment-modal-delete" data-cid="' + commentid + '">' + strings[2] + '</button>' +
        //     '<button class="btn btn-secondary" aria-hidden="true" id="learningcompanions-deletecomment-modal-close" data-action="hide">' + strings[3] + '</button>' +
        //     '</div>'
    }).then(function(modal) {
        console.log('comment id from data:', commentid);

        modal.getRoot().on(ModalEvents.save, function() {
            console.log('about to call ajaxdeletecomment.php with comment id', commentid);
            $.ajax({
                url: M.cfg.wwwroot + '/local/learningcompanions/ajaxreport.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    commentid: commentid
                },
                success: function(data) {
                    // modal.destroy();
                    if (data === 'fail') {
                        // ICTODO: output fail message
                    } else {
                        // ICTODO: output success message
                    }
                    document.dispatchEvent(new CustomEvent('learningcompanions_message_reported'));
                    document.dispatchEvent(new ModalEvents.hidden);
                }
            });
        });
        modal.setSaveButtonText(strings[7]);
        // modal.getRoot().on(ModalEvents.hidden, function () {
        //     modal.destroy();
        // });
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
            url: M.cfg.wwwroot + '/local/learningcompanions/ajax.php',
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
        M.cfg.wwwroot + "/local/learningcompanions/ajaxsubmit.php",
        data
    ).done(function(a,b,c) {
        // ICTODO: give a success message, like with a toast or so
        // reset the form to empty values after successfully sending the form
        $('#learningcompanions_chat form #id_messageeditable').text("");
        $('#learningcompanions_chat form input, #learningcompanions_chat form textarea').each((index, el) => {
            if (el.name == "textarea" || el.type !== "hidden") {
                el.value = '';
            }
        });
    }).fail(function(a,b,c){
        console.warn('Failed sending via AJAX', a, b, c);
        window.alert("couldn't save"); // ICTODO: give proper message, via get_string and ideally with a modal
    }).always(function(){
        // reactivate the form/ungrey it when data has been sent
        $('#learningcompanions_chat #id_messageeditable').css('opacity', '1');
        $('#learningcompanions_chat #id_messageeditable').attr('contenteditable', 'true');
        $('#learningcompanions_chat form input,  #learningcompanions_chat form textarea, #local_learningcompanions_chat-send').attr('disabled', false);
    });
    return false;
};
