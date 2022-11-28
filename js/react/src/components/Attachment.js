import React from "react";

export default function Attachment(props) {
    return (
        <div>
            <a targer="_blank" href={props.attachment.url}>{props.attachment.filename}</a>
        </div>
    )
}