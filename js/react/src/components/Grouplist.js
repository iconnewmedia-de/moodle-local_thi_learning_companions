import React from "react";
import Group from "./Group";
import LoadingIndicator from "./LoadingIndicator";

export default function Grouplist({groups, handleGroupSelect, activeGroupid, loading}) {
    return (
        <div id="learningcompanions_chat-grouplist">
            <LoadingIndicator loading={loading} />
            {groups.map(group => (
                <Group handleGroupSelect={handleGroupSelect} name={group.name} key={group.id} id={group.id} shortdescription={group.shortdescription} description={group.description} imageurl={group.imageurl} latestcomment={group.latestcomment} activeGroupid={activeGroupid} />
            ))}
        </div>
    );
};