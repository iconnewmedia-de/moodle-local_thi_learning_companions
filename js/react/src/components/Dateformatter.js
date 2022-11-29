/* eslint-disable no-undef, no-console */
import React from "react";

export default function Dateformatter({timestamp}) {
    var date = new Date(timestamp * 1000);
    var today = new Date();
    if (today.getDate() == date.getDate() && today.getMonth() == date.getMonth() && today.getFullYear() == date.getFullYear()) {
        var time = date.getHours + ':' + date.getMinutes();
    } else {
        var time = date.toDateString();
    }
    // ICTODO: maybe also display "Saturday", "Friday" etc. instead if it's from the same week
    return (
        <div className="learningcompanions_chat_time">{time}</div>
    )
}