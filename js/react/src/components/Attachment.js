import React from "react";

export default function Attachment(props) {
    return (
        <div>
            <a target="_blank" href={props.attachment.url}>{props.attachment.filename}</a>
        </div>
    )
}