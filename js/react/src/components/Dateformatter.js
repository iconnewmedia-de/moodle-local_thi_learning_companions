export default function Dateformatter({timestamp, format = { year: 'numeric', month: '2-digit', day: '2-digit'}}) {
    timestamp = timestamp ?? 0;
    timestamp = parseInt(timestamp);
    if (timestamp === 0) {
        return <div className="thi_learning_companions_chat_time">-</div>;
    }

    const date = new Date(timestamp * 1000);

    const lastActive = new Intl.DateTimeFormat(undefined, format).format(date);

    return (
        <div className="thi_learning_companions_chat_time">{lastActive}</div>
    )
}
