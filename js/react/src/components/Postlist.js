/* eslint-disable no-undef, no-console */
import React, {useState, useEffect} from "react";
import Post from "./Post";
import GroupHeader from "./GroupHeader";
import LoadingIndicator from "./LoadingIndicator";
import eventBus from "./EventBus";

export default function Postlist(props) {
    if (typeof window.M === "undefined") {
        window.M = {cfg: {wwwroot: ''}};
    }
    const [posts, setPosts] = useState([]);
    const [group, setGroup] = useState({});
    const [activeGroupid, setActiveGroupid] = useState(props.activeGroupid);
    const [chattimer, setChattimer] = useState(0);
    const [loading, setLoading] = useState(true);
    const [reload, setReload] = useState(0);

    eventBus.on('groupchanged', (data) => {
        setActiveGroupid(data.groupid);
    });
    eventBus.on('learningcompanions_message_deleted', () => {
        setReload(reload + 1);
    });
    function getPosts() {
        const controller = new AbortController();
        // if (groupid !== activeGroupid) {
        //     setPosts([]);
        // }
        async function fetchPosts(groupid) {
            const response = await fetch(M.cfg.wwwroot + '/local/learningcompanions/ajaxchat.php?groupid=' + groupid);
            const data = await response.json();
            let posts = Object.values(data.posts);
            setPosts(posts);
            setGroup(data.group);
            setLoading(false);
        }
        fetchPosts(activeGroupid);
        return () => controller.abort();
    }

    useEffect(getPosts, [activeGroupid, chattimer, reload]);

    //Wrap the setInterval in a useEffect hook, so it doesn´t add a new interval on every render.
    useEffect(() => {
        const intervalId = window.setInterval(() => {
            setChattimer(chattimer + 1);
        }, 10000);
        console.log('Adding Interval', intervalId);
        return () => {
            console.log('clearing interval', intervalId);
            window.clearInterval(intervalId);
        }
    }, []);

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
