/* eslint no-console: off, no-unused-vars: off */
define(['jquery','core/str','core/modal_factory', 'core/modal_events'], function($, str, ModalFactory, ModalEvents){
    return {
        'init': function (){
            /**
             * submits the message via AJAX
             * @param {any} e
             * @returns {boolean}
             */
            function sendForm(e) {
                if (e.target.getAttribute("disabled") == true) {
                    return;
                }
                // ICTODO: disable the form/grey it out, while data is getting sent
                e.preventDefault();
                var data = {};
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
            }
            // put send button to the right next to the message box
            // we can't arrange it that way with just Moodle Forms API
            var sendButton = $('#local_learningcompanions_chat-send');
            var messageBox = $('#id_messageeditable');
            messageBox.after(sendButton);
            // add event listener to the send button to handle message sending
            $('#local_learningcompanions_chat-send').on('click', sendForm);

            var strings = [
                {key: 'modal-deletecomment-title', component: 'local_learningcompanions'},
                {key: 'modal-deletecomment-text', component: 'local_learningcompanions'},
                {key: 'modal-deletecomment-okaybutton', component: 'local_learningcompanions'},
                {key: 'modal-editcomment-title', component: 'local_learningcompanions'},
                {key: 'modal-editcomment-okaybutton', component: 'local_learningcompanions'},
            ];

            str.get_strings(strings).then(function(strings) {
                $('#learningcompanions_chat-postlist').on('click', function(e){
                    if (e.target.classList.contains('learningcompanions_delete_comment')) {
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
                        }).then(function (modal) {
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
                                    success: function (data) {
                                        // modal.destroy();
                                        if (data === 'fail') {
                                            // ICTODO: output fail message
                                        } else {
                                            // ICTODO: output success message
                                        }
                                        document.dispatchEvent(new CustomEvent('learningcompanions_message_deleted'));
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
                    }
                    if (e.target.classList.contains('learningcompanions_edit_comment')) {
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
                        }).then(function (modal) {
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
                            ).done(function(a,b,c) {
                                console.log('inside edit comment. a:', a, 'b:', b, 'c:', c);
                                modal.setText(a.text);
                            })
                            // modal.getRoot().on(ModalEvents.hidden, function () {
                            //     modal.destroy();
                            // });
                            modal.show();
                        });
                    }

                });
            });
        }
    };
});
