/* eslint-disable no-undef, no-console */
import Attachmentlist from "./Attachmentlist";
import DeleteButton from "./PostButtons/DeleteButton";
import ReportButton from "./PostButtons/ReportButton";
import RatingButton from "./PostButtons/RatingButton";
import Dateformatter from './Dateformatter';

export default function Post({highlighted, post, isPreview, questionid}) {
    const {author, id, timecreated, comment, attachments, flagged: reported, timedeleted: deleted} = post;
    let showDeleteButton = false;
    let showReportButton = false;

    // let showRatingButton = true;
    // console.log('questionid: ', questionid);
    // console.log('author.id: ', window.learningcompanions_chat_userid);
    let showRatingButton = (questionid > 0 && +author.id !== +window.learningcompanions_chat_userid);

    // ICTODO: also get the information if the current user has already rated the post
    let cssClass;

    let isDeleted = !(typeof deleted === "undefined" || deleted === null || deleted === 0);
    // ICTODO: find a way to use get_string with React. Perhaps a global variable that holds the translated strings
    if (typeof window.learningcompanions_chat_userid !== undefined
        && +author.id === +window.learningcompanions_chat_userid
        && !isDeleted) {
        showDeleteButton = true;
        cssClass = 'learningcompanions_chat-my-post';
    } else if (!isDeleted) {
        showReportButton = true;
        cssClass = 'learningcompanions_chat-other-post';
    }

    !!+reported && (cssClass += ' learningcompanions_chat-reported-post');

    return (
        <div id={`learningcompanions_chat-post-${id}`} className={`learningcompanions_chat-post ${cssClass}`}>
            <strong>{author.firstname} {author.lastname}</strong><br />
            <em><Dateformatter timestamp={timecreated} format={{ year: 'numeric', month: '2-digit', day: '2-digit', weekday: 'long'}}/></em><br />

            {!+deleted && <div dangerouslySetInnerHTML={{__html: comment}}></div>}
            {!!+deleted && <div><i>Message Deleted</i></div>}

            {!+deleted && !!attachments.length && <Attachmentlist attachments={attachments} />}
            <div className="action-button-wrapper">
                {!isPreview && showDeleteButton && <DeleteButton id={id} />}
                {!isPreview && showReportButton && !+reported && <ReportButton id={id} />}
                {!isPreview && showRatingButton && <RatingButton id={id} isratedbyuser={post.isratedbyuser} />}
            </div>
            {highlighted && (
                <>
                    <br />
                    <span className="highlighted-post">Highlighted</span>
                </>)}
        </div>
    )
};
