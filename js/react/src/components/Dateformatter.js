/* eslint-disable no-undef, no-console */
import React from "react";

export default function Dateformatter({timestamp}) {
    timestamp = timestamp ?? 0;
    timestamp = parseInt(timestamp);
    if (timestamp === 0) {
        return <div className="learningcompanions_chat_time"></div>;
    }

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
