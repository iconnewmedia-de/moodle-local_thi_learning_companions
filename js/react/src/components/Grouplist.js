import React from "react";
import Group from "./Group";

export default function Grouplist({groups}) {
    return (
        <div id="learningcompanions_chat-grouplist">
            {groups.map(group => (
                <Group name={group.name} key={group.id} id={group.id} shortdescription={group.shortdescription} description={group.description} imageurl={group.imageurl} />
            ))}
        </div>
    );
};