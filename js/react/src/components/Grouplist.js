/* eslint-disable no-undef, no-console */
import {useState, useEffect} from "react";
import Group from "./Group";
import LoadingIndicator from "./LoadingIndicator";
import eventBus from "../helpers/EventBus";

const previewSelector = ".js-chat-preview";
const messageInputSelector = "#fitem_id_message";
const attachmentsSelector = "#fitem_id_attachments";
const requiredHintSelector = '.fdescription.required';

export default function Grouplist({activeGroupid, previewGroup}) {
    if (typeof window.M === "undefined") {
        window.M = {cfg: {wwwroot: ''}};
    }

    const [groups, setGroups] = useState([]);
    const [activeGroupId, setActiveGroupId] = useState(activeGroupid);
    const [grouptimer, setGrouptimer] = useState(0);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        const chatid = groups.find(group => +group.id === +activeGroupId)?.chatid;
        const isPreviewGroup = groups.find(group => +group.id === +activeGroupId)?.isPreviewGroup ?? false;

        let newChatValue = chatid;
        if (isPreviewGroup) {
            console.log('I make the input invisible');
            newChatValue = '';
            document.querySelector(previewSelector)?.classList.replace('d-none','d-flex');
            document.querySelectorAll(`${messageInputSelector}, ${attachmentsSelector}, ${requiredHintSelector}`).forEach(el => el.classList.add('d-none'));
        } else {
            console.log('I make the input Visible');
            document.querySelector(previewSelector)?.classList.replace('d-flex', 'd-none');
            document.querySelectorAll(`${messageInputSelector}, ${attachmentsSelector}, ${requiredHintSelector}`).forEach(el => el.classList.remove('d-none'));
        }

        document.querySelector('input[name="chatid"]').value = newChatValue;
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

    function getGroups() {
        const controller = new AbortController();

        const previewGroup = (new URLSearchParams(window.location.search)).get('previewGroup');
        const urlParams = new URLSearchParams();

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
    useEffect(getGroups, [grouptimer]);

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
