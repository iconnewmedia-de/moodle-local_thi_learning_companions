import Attachment from "./Attachment";

export default function Attachmentlist({attachments = []}) {
    return (
        attachments.map(attachment => (
            <Attachment attachment={attachment} />
        ))
    );
};
