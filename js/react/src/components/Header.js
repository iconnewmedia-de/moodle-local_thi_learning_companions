import QuestionHeader from "./QuestionHeader.js";
import GroupHeader from "./GroupHeader.js";

export default function Header({questionid, group}) {
    if (questionid) return <QuestionHeader questionid={questionid} />;
    if (group) return <GroupHeader group={group} />;
}
