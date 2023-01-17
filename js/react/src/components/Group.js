/* eslint-disable no-undef, no-console */
import Dateformatter from "./Dateformatter";

export default function Group({handleGroupSelect, name, id, description, chatid, shortdescription, imageurl, latestcomment, activeGroupid}) {
    const changeGroup = function() {
        handleGroupSelect(id, chatid);
    };

    let cssclasses = 'd-flex learningcompanions_chat-group';

    if (typeof activeGroupid !== "undefined" && parseInt(activeGroupid) === parseInt(id)) {
        cssclasses += ' learningcompanions_currentgroup';
    }

    return (
        <div onClick={changeGroup} id={"learningcompanions_chat-group-" + id} className={cssclasses}>
            <img className="learningcompanions_group_image_small" src={imageurl} />
            <div className="learningcompanions_group_infos">
                <em>{name}</em><br />
                <span>{shortdescription}</span>
            </div>
            <Dateformatter timestamp={latestcomment} />
        </div>
    );
};
