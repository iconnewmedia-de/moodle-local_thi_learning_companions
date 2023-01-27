/* eslint-disable no-undef, no-console */
import {useState, useEffect} from "react";
import Group from "./Group";
import LoadingIndicator from "./LoadingIndicator";
import {hideForm, useSetChatInput} from "../hooks/moodleHelpers.js";
import Postlist from "./Postlist.js";
import ReactDOM from "react-dom";

const shouldIncludeId = (new URLSearchParams(window.location.search)).get('groupid') || window.learningcompanions_groupid;

export default function Grouplist({activeGroupid}) {
    if (typeof window.M === "undefined") {
        window.M = {cfg: {wwwroot: ''}};
    }

    const [groups, setGroups] = useState([]);
    const [activeGroupId, setActiveGroupId] = useState(activeGroupid);
    const [updateGroups, setUpdateGroups] = useState(false);
    const [isLoading, setIsLoading] = useState(true);

    if (activeGroupId === undefined && groups.length) {
        setActiveGroupId(groups[0].id);
    }

    useEffect(() => {
        const group = groups.find(group => +group.id === +activeGroupId);
        const chatid = group?.chatid;
        const isPreviewGroup = group?.isPreviewGroup ?? false;

        useSetChatInput(isPreviewGroup, chatid);
        console.log(`setting active group id to: ${activeGroupId} and chatid to: ${chatid}`);
    }, [activeGroupId, groups]);

    function handleGroupSelect(groupid) {

        const searchParams = new URLSearchParams(window.location.search);
        searchParams.set('groupid', groupid); //Update the current group Param
        searchParams.delete('postId'); //Remove the postId Param

        window.history.replaceState(null,
            "Chat",
            `${window.M.cfg.wwwroot}/local/learningcompanions/chat.php?${searchParams}`
        );
        setActiveGroupId(groupid);
    }

    useEffect(() => {
        const urlParams = new URLSearchParams();
        const controller = new AbortController();

        if (shouldIncludeId) {
            urlParams.set('shouldIncludeId', shouldIncludeId);
        }

        fetch(`${M.cfg.wwwroot}/local/learningcompanions/ajaxgrouplist.php?${urlParams}`, {
            signal: controller.signal
        })
            .then(response => response.json())
            .then(({groups}) => {
                console.log('Groups', groups);
                setGroups(groups);
                setIsLoading(false);

                if (groups.length === 0) {
                    hideForm();
                }
            })
            .catch(error => {
                if (error.name !== "AbortError") {
                    console.log("Error: " + error.message);
                }
            });
    }, [updateGroups]);

    //Wrap the setInterval in a useEffect hook, so it doesn´t add a new interval on every render.
    useEffect(() => {
        const intervalId = window.setInterval(() => {
            setUpdateGroups(ug => !ug); //Toggle the updateGroups state
        }, 50000);

        return () => {
            window.clearInterval(intervalId);
        }
    }, []);

    const chatPlaceholder = document.getElementById('learningcompanions_chat-content');
    return (
        <>
            <div id="learningcompanions_chat-grouplist">
                {isLoading && <LoadingIndicator/>}
                {groups.map(group => (
                    <Group key={group.id} handleGroupSelect={handleGroupSelect} group={group} activeGroupid={activeGroupId}/>
                ))}
                {groups.length === 0 && !isLoading && <p>No groups found</p>}
            </div>
            {ReactDOM.createPortal(<Postlist activeGroupid={activeGroupId} groups={groups}/>, chatPlaceholder)}
        </>
    );
};
