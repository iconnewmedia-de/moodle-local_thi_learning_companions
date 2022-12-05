/* eslint-disable no-undef, no-console */
import React from "react";
import Group from "./Group";
import LoadingIndicator from "./LoadingIndicator";
import eventBus from "./EventBus";

export default function Grouplist(props) {
    if (typeof M === "undefined") {
        var M = {cfg: {wwwroot: ''}};
    }
    const [groups, setGroups] = React.useState([]);
    const [activeGroupid, setActiveGroupid] = React.useState(props.activeGroupid);
    const [grouptimer, setGrouptimer] = React.useState(0);
    const [loading, setLoading] = React.useState(true);
    window.setInterval(() => {
        setGrouptimer(grouptimer + 1);
    }, 50000);
    function handleGroupSelect(groupid) {
        eventBus.dispatch('groupchanged', {groupid: groupid});
        setActiveGroupid(groupid);
    }

    function getGroups() {
        const controller = new AbortController();
        async function fetchGroups() {
            const groups = await fetch(M.cfg.wwwroot + '/local/learningcompanions/ajaxgrouplist.php');
            const data = await groups.json();
            setGroups(data.groups);
            setLoading(false);
        }
        fetchGroups();
        return () => controller.abort();
    }
    React.useEffect(() => getGroups(), [activeGroupid, grouptimer]);
    return (
        <div id="learningcompanions_chat-grouplist">
            <LoadingIndicator loading={loading} />
            {groups.map(group => (
                <Group handleGroupSelect={handleGroupSelect} name={group.name} key={group.id} id={group.id} shortdescription={group.shortdescription} description={group.description} imageurl={group.imageurl} latestcomment={group.latestcomment} activeGroupid={activeGroupid} />
            ))}
        </div>
    );
};