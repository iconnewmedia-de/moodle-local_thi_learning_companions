/* eslint-disable no-undef, no-console */
import Attachmentlist from "./Attachmentlist";
import DeleteButton from "./PostButtons/DeleteButton";
import ReportButton from "./PostButtons/ReportButton";

export default function Post({author, id, datetime, comment, attachments, reported, deleted, highlighted}) {
    let showDeleteButton = false;
    let showReportButton = false;
    let cssClass;

    // ICTODO: find a way to use get_string with React. Perhaps a global variable that holds the translated strings
    if (typeof window.learningcompanions_chat_userid !== undefined && +author.id === +window.learningcompanions_chat_userid) {
        showDeleteButton = true;
        cssClass = 'learningcompanions_chat-my-post';
    } else {
        showReportButton = true;
        cssClass = 'learningcompanions_chat-other-post';
    }

    reported && (cssClass += ' learningcompanions_chat-reported-post');

    return (
        <div id={`learningcompanions_chat-post-${id}`} className={`learningcompanions_chat-post ${cssClass}`}>
            <strong>{author.firstname} {author.lastname}</strong><br />
            <em>{datetime}</em><br />

            {!deleted && <div dangerouslySetInnerHTML={{__html: comment}}></div>}
            {deleted && <div><i>Message Deleted</i></div>}

            {!!attachments.length && <Attachmentlist attachments={attachments} />}
            <div className="action-button-wrapper">
                {showDeleteButton && <DeleteButton id={id} />}
                {showReportButton && !reported && <ReportButton id={id} />}
            </div>
            <span>ID: {id}</span>
            {highlighted && (
                <>
                    <br />
                    <span className="highlighted-post">Highlighted</span>
                </>)}
        </div>
    )
};
