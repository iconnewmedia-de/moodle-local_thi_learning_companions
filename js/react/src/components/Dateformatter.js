/* eslint-disable no-undef, no-console */
import React from "react";

export default function Dateformatter({timestamp}) {
    timestamp = timestamp ?? 0;
    console.log('timestamp: ', timestamp);
    timestamp = parseInt(timestamp);
    if (timestamp === 0) {
        return <div className="learningcompanions_chat_time"></div>;
    }

    // if (timestamp === 0) {
    //     var time = '';
    // } else {
    //     var date = new Date(timestamp * 1000);
    //     var today = new Date();
    //     if (today.getDate() == date.getDate() && today.getMonth() == date.getMonth() && today.getFullYear() == date.getFullYear()) {
    //         var time = date.getHours() + ':' + date.getMinutes();
    //     } else {
    //         var time = date.toDateString();
    //     }


    const lastActive = new Intl.DateTimeFormat(undefined, {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    }).format(date);
    console.log('lastActive: ', lastActive);

    // }
    // ICTODO: maybe also display "Saturday", "Friday" etc. instead if it's from the same week
    return (
        <div className="learningcompanions_chat_time">{lastActive}</div>
    )
}
