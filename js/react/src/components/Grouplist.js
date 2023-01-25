/* eslint-disable no-undef, no-console */
import {useState, useEffect} from "react";
import Group from "./Group";
import LoadingIndicator from "./LoadingIndicator";
import eventBus from "../helpers/EventBus";
import {useSetChatInput} from "../hooks/moodleHelpers.js";

const shouldIncludeId = (new URLSearchParams(window.location.search)).get('groupid');

export default function Grouplist({activeGroupid, previewGroup}) {
    if (typeof window.M === "undefined") {
        window.M = {cfg: {wwwroot: ''}};
    }

    const [groups, setGroups] = useState([]);
    const [activeGroupId, setActiveGroupId] = useState(activeGroupid);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        const group = groups.find(group => +group.id === +activeGroupId);
        const chatid = group?.chatid;
        const isPreviewGroup = group?.isPreviewGroup ?? false;

        useSetChatInput(isPreviewGroup, chatid);
        console.log(`setting active group id to: ${activeGroupId} and chatid to: ${chatid}`);
    }, [activeGroupId, groups]);

    function handleGroupSelect(groupid) {
        eventBus.dispatch(eventBus.events.GROUP_CHANGED, {groupid});

        const searchParams = new URLSearchParams(window.location.search);
        searchParams.set('groupid', groupid); //Update the current group Param
        searchParams.delete('postId'); //Remove the postId Param

        window.history.replaceState(null,
            "Chat",
            `${window.M.cfg.wwwroot}/local/learningcompanions/chat.php?${searchParams}`
        );
        setActiveGroupId(groupid);
    }

    //Wrap the setInterval in a useEffect hook, so it doesn´t add a new interval on every render.
    useEffect(() => {
        const controller = new AbortController();
        const intervalId = window.setInterval(() => {
            const urlParams = new URLSearchParams();

            if (shouldIncludeId) {
                urlParams.set('shouldIncludeId', shouldIncludeId);
            }

            fetch(`${M.cfg.wwwroot}/local/learningcompanions/ajaxgrouplist.php?${urlParams}`, {
                signal: controller.signal
            })
                .then(response => response.json())
                .then(({groups}) => {
                    setGroups(groups);
                    setIsLoading(false);
                    eventBus.dispatch(eventBus.events.GROUPS_UPDATED, {groups});
                })
                .catch(error => {
                    if (error.name !== "AbortError") {
                        console.log("Error: " + error.message);
                    }
                });
        }, 5000);

        return () => {
            controller.abort();
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
