/* eslint-disable no-undef, no-console */
import React from "react";
import Attachmentlist from "./Attachmentlist";
import EditButton from "./PostButtons/EditButton";
import DeleteButton from "./PostButtons/DeleteButton";
import ReportButton from "./PostButtons/ReportButton";

export default function Post({author, id, datetime, comment, attachments}) {
    let attach;
    if (attachments && attachments.length) {
        attach = <Attachmentlist attachments={attachments} />
    } else {
        attach = '';
    }
    let showEditButton = false;
    let showDeleteButton = false;
    let showReportButton = false;
    let cssClass;

    // ICTODO: find a way to use get_string with React. Perhaps a global variable that holds the translated strings
    if (typeof window.learningcompanions_chat_userid !== undefined && +author.id === +window.learningcompanions_chat_userid) {
        showEditButton = true;
        showDeleteButton = true;
        cssClass = 'learningcompanions_chat-my-post';
    } else {
        showReportButton = true;
        cssClass = 'learningcompanions_chat-other-post';
    }

    return (
        <div id={"learningcompanions_chat-post-" + id} className={'learningcompanions_chat-post ' + cssClass}>
            <strong>{author.firstname} {author.lastname}</strong><br />
            <em>{datetime}</em><br />
            <div dangerouslySetInnerHTML={{__html: comment}}></div>
            {attach}
            <div className="action-button-wrapper">
                {showEditButton && <EditButton id={id} />}
                {showDeleteButton && <DeleteButton id={id} />}
                {showReportButton && <ReportButton id={id} />}
            </div>
        </div>
    )
};
