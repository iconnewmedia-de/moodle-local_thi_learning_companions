import Dateformatter from "./Dateformatter";

export default function Group({handleGroupSelect, group, activeGroupid}) {
    const {name, id, description, chatid, shortdescription, imageurl, lastcomment, latestcomment, isPreview, timecreated, has_new_comments, comments_since_last_visit} = group;

    const changeGroup = function() {
        handleGroupSelect(id, chatid);
    };

    let cssclasses = 'd-flex learningcompanions_chat-group';

    if (typeof activeGroupid !== "undefined" && parseInt(activeGroupid) === parseInt(id)) {
        cssclasses += ' learningcompanions_currentgroup';
    }

    if (isPreview) {
        cssclasses += ' learningcompanions_previewgroup';
    }

    return (
        <div onClick={changeGroup} id={"learningcompanions_chat-group-" + id} className={cssclasses}>
            <img className="learningcompanions_group_image_small" src={imageurl}/>
            <div className="learningcompanions_group_infos">
                <em title={name}>{name}</em><br />
                <span>{lastcomment}</span>
            </div>
            <Dateformatter timestamp={latestcomment ?? timecreated} />
            <div className="learningcompanions_mygroups_group_newposts_count">
                {has_new_comments &&  <span className="learningcompanions_mygroups_group_newposts_count_circle">
                    {comments_since_last_visit}
                </span>}
            </div>
        </div>
    );
};
