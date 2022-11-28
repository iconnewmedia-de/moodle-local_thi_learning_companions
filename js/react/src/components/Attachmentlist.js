import React from "react";
import Attachment from "./Attachment";

export default function Attachmentlist({attachments}) {
    if (!attachments || attachments.length === 0) {
        return;
    }
    return (
        attachments.map(attachment => (
            <Attachment attachment={attachment} />
        ))
    );
};