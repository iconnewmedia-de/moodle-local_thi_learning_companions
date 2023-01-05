export default function Attachment({attachment}) {
    const {url, filename} = attachment;

    return (
        <div>
            <a target="_blank" href={url}>{filename}</a>
        </div>
    )
}

