import React from "react";
import Group from "./Group";

export default function Grouplist({groups, handleGroupSelect}) {
    return (
        <div id="learningcompanions_chat-grouplist">
            {groups.map(group => (
                <Group handleGroupSelect={handleGroupSelect} name={group.name} key={group.id} id={group.id} shortdescription={group.shortdescription} description={group.description} imageurl={group.imageurl} />
            ))}
        </div>
    );
};