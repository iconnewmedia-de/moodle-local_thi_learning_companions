/* eslint-disable no-undef, no-console */
import React from "react";
import Attachmentlist from "./Attachmentlist";

export default function Post({author, id, datetime, comment, attachments}) {
    let attach;
    // console.log('attachments: ', attachments);
    if (attachments && attachments.length > 0) {
        attach = <Attachmentlist attachments={attachments} />
    } else {
        attach = ' ';
    }
    let button1;
    let button2;
    let cssClass;

    if (typeof learningcompanions_chat_userid !== "undefined") {
        // console.log('Post has author:', author, 'learningcompanions_chat_userid:', learningcompanions_chat_userid);
    }
    // ICTODO: find a way to use get_string with React. Perhaps a global variable that holds the translated strings
    if (typeof learningcompanions_chat_userid !== "undefined" && parseInt(author.id) === parseInt(learningcompanions_chat_userid)) {
        button1 = <a href='#' data-id={id} className='learningcompanions_edit_comment'>edit</a>
        button2 = <a href="#" data-id={id} className='learningcompanions_delete_comment'>delete</a>
        cssClass = 'learningcompanions_chat-my-post';
    } else {
        button1 = <a href="#" data-id={id} className='learningcompanions_report_comment'>report</a>;
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
