/* eslint-disable no-undef, no-console */
import React from "react";
import Attachmentlist from "./Attachmentlist";

export default function Post({author, id, datetime, comment, attachments}) {
    let attach;
    if (attachments && attachments.length > 0) {
        attach = <Attachmentlist attachments={attachments} />
    } else {
        attach = ' ';
    }
    let button1;
    let button2;
    let cssClass;
    function report(id) {
        var self = this;
        Y.use('moodle-core-notification-confirm', function() {
            var confirm = new M.core.confirm({
                title:      M.util.get_string('confirm', 'moodle'),
                question:   M.util.get_string('overridenoneconfirm', 'gradereport_singleview'),
            });
            confirm.on('complete-yes', function() {
                confirm.hide();
                confirm.destroy();
                Y.all('input[name^=' + type + ']').each(toggle(link.hasClass('all')));
            }, self);
            confirm.show();
        });
    }
    if (typeof learningcompanions_chat_userid !== "undefined") {
        // console.log('Post has author:', author, 'learningcompanions_chat_userid:', learningcompanions_chat_userid);
    }
    if (typeof learningcompanions_chat_userid !== "undefined" && parseInt(author.id) === parseInt(learningcompanions_chat_userid)) {
        button1 = <a href={M.cfg.wwwroot + '/blocks/learningcompanions_chat/ajaxchat.php?edit=' + id} className='learningcompanions_edit_comment'>edit</a>
        button2 = <a href="#" className='learningcompanions_delete_comment'>delete</a>
        cssClass = 'learningcompanions_chat-my-post';
    } else {
        button1 = <a href="#" onClick={(e) => this.report(id, e)} className='learningcompanions_report_comment'>report</a>;
        button2 = '';
        cssClass = 'learningcompanions_chat-other-post';
    }
    return (
        <div id={"learningcompanions_chat-post-" + id} className={'learningcompanions_chat-post ' + cssClass}>
            <strong>{author.firstname} {author.lastname}</strong><br />
            <em>{datetime}</em><br />
            <div dangerouslySetInnerHTML={{__html: comment}}></div>
            {attach}
            {button1} {button2}
        </div>
    )
};