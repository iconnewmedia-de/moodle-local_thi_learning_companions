/* eslint-disable no-undef, no-console */
import React from "react";
import Post from "./Post";
import GroupHeader from "./GroupHeader";
import LoadingIndicator from "./LoadingIndicator";
import eventBus from "./EventBus";
export default function Postlist(props) {
    if (typeof M === "undefined") {
        var M = {cfg: {wwwroot: ''}};
    }
    const [posts, setPosts] = React.useState([]);
    const [group, setGroup] = React.useState({});
    const [activeGroupid, setActiveGroupid] = React.useState(props.activeGroupid);
    const [chattimer, setChattimer] = React.useState(0);
    const [loading, setLoading] = React.useState(true);
    window.setInterval(() => {
        setChattimer(chattimer + 1);
    }, 10000);
    eventBus.on('groupchanged', (data) => {
        setActiveGroupid(data.groupid);
    });
    function getPosts(groupid) {
        const controller = new AbortController();
        if (groupid !== activeGroupid) {
            setPosts([]);
        }
        async function fetchPosts(groupid) {
            const response = await fetch(M.cfg.wwwroot + '/local/learningcompanions/ajaxchat.php?groupid=' + groupid);
            const data = await response.json();
            let posts = Object.values(data.posts);
            setPosts(posts);
            setGroup(data.group);
            setLoading(false);
        }
        fetchPosts(groupid);
        return () => controller.abort();
    }
    React.useEffect(() => getPosts(activeGroupid), [activeGroupid, chattimer]);
    return (
        <div id="learningcompanions_chat-postlist">
            <LoadingIndicator loading={loading} />
            <GroupHeader group={group}/>
            {posts.map(post => {
                    return (
                        <Post author={post.author} key={post.id} id={post.id} datetime={post.datetime}
                              comment={post.comment} attachments={post.attachments}/>
                    );
                }
            )}
        </div>
    );
};