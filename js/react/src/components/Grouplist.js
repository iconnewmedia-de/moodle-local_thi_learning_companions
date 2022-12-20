/* eslint-disable no-undef, no-console */
import React, {useState, useEffect} from "react";
import Group from "./Group";
import LoadingIndicator from "./LoadingIndicator";
import eventBus from "./EventBus";

export default function Grouplist(props) {
    if (typeof window.M === "undefined") {
        window.M = {cfg: {wwwroot: ''}};
    }
    const [groups, setGroups] = useState([]);
    const [activeGroupid, setActiveGroupid] = useState(props.activeGroupid);
    const [grouptimer, setGrouptimer] = useState(0);
    const [loading, setLoading] = useState(true);

    function handleGroupSelect(groupid) {
        eventBus.dispatch('groupchanged', {groupid: groupid});
        setActiveGroupid(groupid);
        document.querySelector('input[name="chatid"]').value = groupid;
        console.log('setting active group id to: ', groupid);
    }

    function getGroups() {
        const controller = new AbortController();
        async function fetchGroups() {
            const groups = await fetch(M.cfg.wwwroot + '/local/learningcompanions/ajaxgrouplist.php');
            const data = await groups.json();
            setGroups(data.groups);
            // console.log('got groups via AJAX:', data.groups);
            setLoading(false);
        }
        fetchGroups();
        return () => controller.abort();
    }
    useEffect(getGroups, [activeGroupid, grouptimer]);

    //Wrap the setInterval in a useEffect hook, so it doesn´t add a new interval on every render.
    useEffect(() => {
        const intervalId = window.setInterval(() => {
            setGrouptimer(grouptimer + 1);
        }, 50000);

        return () => {
            window.clearInterval(intervalId);
        }
    });
    return (
        <div id="learningcompanions_chat-grouplist s">
            <LoadingIndicator loading={loading} />
            {groups.map(group => (
                <Group handleGroupSelect={handleGroupSelect} name={group.name} key={group.id} id={group.id} shortdescription={group.shortdescription} description={group.description} imageurl={group.imageurl} latestcomment={group.latestcomment} activeGroupid={activeGroupid} />
            ))}
        </div>
    );
};
