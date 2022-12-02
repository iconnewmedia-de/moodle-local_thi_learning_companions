/* eslint-disable no-undef, no-console */
import './App.css';
import React from "react";
import Postlist from "./components/Postlist";
import Grouplist from "./components/Grouplist";
import eventBus from "./components/eventBus";

function App(props) {
    if (typeof M === "undefined") {
        var M = {cfg: {wwwroot: ''}};
    }
    const [posts, setPosts] = React.useState(props.posts);
    const [group, setGroup] = React.useState({});
    const [groups, setGroups] = React.useState(props.groups);
    const [activeGroupid, setActiveGroupid] = React.useState(props.activeGroupid);
    const [grouptimer, setGrouptimer] = React.useState(0);
    const [chattimer, setChattimer] = React.useState(0);
    const [postsLoading, setPostsLoading] = React.useState(true);
    const [groupsLoading, setGroupsLoading] = React.useState(true);
    window.setInterval(() => {
        setGrouptimer(grouptimer + 1);
    }, 50000);
    window.setInterval(() => {
        setChattimer(chattimer + 1);
    }, 10000);

    function handleGroupSelect(groupid) {
        eventBus.dispatch('groupchanged', {groupid: groupid});
    }
    eventBus.on('groupchanged', (data) => {
        setActiveGroupid(data.groupid);
    });

    function getPosts(groupid) {
        if (props.component !== 'chat') {
            return;
        }
        const controller = new AbortController();
        setPosts([]);
        setPostsLoading(true);
        async function fetchPosts(groupid) {
            const response = await fetch(M.cfg.wwwroot + '/local/learningcompanions/ajaxchat.php?groupid=' + groupid);
            const data = await response.json();
            let posts = Object.values(data.posts);
            setPosts(posts);
            setPostsLoading(false);
            setGroup(data.group);
        }
        fetchPosts(groupid);
        return () => controller.abort();
    }

    function getGroups() {
        const controller = new AbortController();
        setGroupsLoading(true);
        async function fetchGroups() {
            const groups = await fetch(M.cfg.wwwroot + '/local/learningcompanions/ajaxgrouplist.php');
            const data = await groups.json();
            setGroups(data.groups);
            setGroupsLoading(false);
        }
        fetchGroups();
        return () => controller.abort();
    }

    React.useEffect(() => getPosts(activeGroupid), [activeGroupid, chattimer]);
    React.useEffect(() => getGroups(), [activeGroupid, grouptimer]);

    return (
        <div>
            {props.component === 'groups' &&
                <Grouplist groups={groups} handleGroupSelect={handleGroupSelect} activeGroupid={activeGroupid} loading={groupsLoading}/>
            }
            {props.component === 'chat' &&
                <Postlist posts={posts} group={group} loading={postsLoading}/>
            }
        </div>
    );
}

export default App;
