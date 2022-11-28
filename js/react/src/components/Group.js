/* eslint-disable no-undef, no-console */
import React from "react";
import Dateformatter from "./Dateformatter";

export default function Group({name, id, description, shortdescription, imageurl, latestcomment}) {
    return (
        <div id={"learningcompanions_chat-group-" + id}>
            <hr />
            <Dateformatter timestamp={latestcomment} />
            <img className="learningcompanions_group_image_small" src={imageurl} />
            <strong>{name}</strong><br />
            <em>{shortdescription}</em><br />
        </div>
    )
};