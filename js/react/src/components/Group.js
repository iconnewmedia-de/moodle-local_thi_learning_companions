import Dateformatter from "./Dateformatter";

export default function Group({handleGroupSelect, group, activeGroupid}) {
    const {name, id, description, chatid, shortdescription, imageurl, lastcomment, latestcomment, isPreview, timecreated, has_new_comments, comments_since_last_visit} = group;

    const changeGroup = function() {
        handleGroupSelect(id, chatid);
    };

    let cssclasses = 'd-flex thi_learning_companions_chat-group';

    if (typeof activeGroupid !== "undefined" && parseInt(activeGroupid) === parseInt(id)) {
        cssclasses += ' thi_learning_companions_currentgroup';
    }

    if (isPreview) {
        cssclasses += ' thi_learning_companions_previewgroup';
    }

    return (
        <div onClick={changeGroup} id={"thi_learning_companions_chat-group-" + id} className={cssclasses}>
            <img className="thi_learning_companions_group_image_small" src={imageurl}/>
            <div className="thi_learning_companions_group_infos">
                <em title={name}>{name}</em><br />
                <span>{lastcomment}</span>
            </div>
            <Dateformatter timestamp={latestcomment ?? timecreated} />
            <div className="thi_learning_companions_mygroups_group_newposts_count">
                {has_new_comments &&  <span className="thi_learning_companions_mygroups_group_newposts_count_circle">
                    {comments_since_last_visit}
                </span>}
            </div>
        </div>
    );
};
