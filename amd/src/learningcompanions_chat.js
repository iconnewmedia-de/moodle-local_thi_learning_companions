/* eslint no-console: off, no-unused-vars: off */
define(['jquery'], function($){
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
        }
    };
});
