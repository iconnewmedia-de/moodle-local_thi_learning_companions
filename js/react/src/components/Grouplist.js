/* eslint-disable no-undef, no-console */
import { useState, useEffect } from "react";
import Group from "./Group";
import LoadingIndicator from "./LoadingIndicator";
import eventBus from "../helpers/EventBus";

export default function Grouplist({activeGroupid, previewGroup}) {
    if (typeof window.M === "undefined") {
        window.M = {cfg: {wwwroot: ''}};
    }

    const [groups, setGroups] = useState([]);
    const [activeGroupId, setActiveGroupId] = useState(activeGroupid);
    const [grouptimer, setGrouptimer] = useState(0);
    const [isLoading, setIsLoading] = useState(true);

    function handleGroupSelect(groupid, chatid) {
        eventBus.dispatch(eventBus.events.GROUP_CHANGED, {groupid});

        const searchParams = new URLSearchParams(window.location.search);
        searchParams.set('groupid', groupid); //Update the current group Param
        searchParams.delete('postId'); //Remove the postId Param

        window.history.replaceState(null,
            "Chat",
            `${window.M.cfg.wwwroot}/local/learningcompanions/chat.php?${searchParams}`
        );
        setActiveGroupId(groupid);
        document.querySelector('input[name="chatid"]').value = chatid;
        console.log(`setting active group id to: ${groupid} and chatid to: ${chatid}`);
    }

    function getGroups() {
        const controller = new AbortController();

        const previewGroup = (new URLSearchParams(window.location.search)).get('previewGroup');
        const urlParams = new URLSearchParams();

        let extention = '';
        if (previewGroup) {
            urlParams.set('previewGroup', previewGroup);
        }

        fetch(`${M.cfg.wwwroot}/local/learningcompanions/ajaxgrouplist.php?${urlParams}`, {
            signal: controller.signal
        })
        .then(response => response.json())
        .then(({groups}) => {
            setGroups(groups);
            setIsLoading(false);
        })
        .catch(error => {
            if (error.name !== "AbortError") {
                console.log("Error: " + error.message);
            }
        });

        return () => controller.abort();
    }
    useEffect(getGroups, [activeGroupId, grouptimer]);

    //Wrap the setInterval in a useEffect hook, so it doesn´t add a new interval on every render.
    useEffect(() => {
        const intervalId = window.setInterval(() => {
            setGrouptimer((grouptimer) => grouptimer + 1);
        }, 50000);

        return () => {
            window.clearInterval(intervalId);
        }
    }, []);

    return (
        <div id="learningcompanions_chat-grouplist">
            {isLoading && <LoadingIndicator/>}
            {groups.map(group => (
                <Group key={group.id} handleGroupSelect={handleGroupSelect} group={group} activeGroupid={activeGroupId} />
            ))}
        </div>
    );
};
