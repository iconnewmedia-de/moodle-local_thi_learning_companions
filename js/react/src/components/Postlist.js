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
    const [page, setPage] = useState(1);
    const [activeGroupid, setActiveGroupid] = useState(props.activeGroupid);
    const [chattimer, setChattimer] = useState(0);
    const [loading, setLoading] = useState(true);
    const [reload, setReload] = useState(0);
    const [lastPostId, setLastPostId] = useState(null);

    eventBus.on(eventBus.events.GROUP_CHANGED, (data) => {
        setActiveGroupid(data.groupid);
        setPage(1);
    });
    eventBus.on(eventBus.events.MESSAGE_DELETED, () => {
        setReload(reload + 1);
    });

    function getMorePosts() {
        const controller = new AbortController();

        fetch(M.cfg.wwwroot + '/local/learningcompanions/ajaxchat.php?groupid=' + activeGroupid + '&page='+page, {
            signal: controller.signal
        })
        .then(response => response.json())
        .then(data => {
            const newPosts = Object.values(data.posts);
            console.log(data);
            setPosts((posts) => [...posts, ...newPosts]);
        }).catch(error => {
            if (error.name === 'AbortError') {
                console.log('Fetch aborted');
            } else {
                console.log(error);
            }
        });

        return () => controller.abort();
    }

    const getInitialPosts = () => {
        const controller = new AbortController();

        fetch(M.cfg.wwwroot + '/local/learningcompanions/ajaxchat.php?groupid=' + activeGroupid, {
            signal: controller.signal
        })
        .then(response => response.json())
        .then(data => {
            const initialPosts = Object.values(data.posts).reverse();
            setPosts(initialPosts);
            setGroup(data.group);
            setLoading(false);
            // Get the Id of the last element
            setLastPostId(initialPosts[0]?.id ?? 0);
            console.log('Last post id: ' + lastPostId);
        }).catch(error => {
            if (error.name === 'AbortError') {
                console.log('Fetch aborted');
            } else {
                console.log(error);
            }
        });

        return () => controller.abort();
    };

    const getNewPosts = () => {
        if (lastPostId === null) return;

        console.log('Getting new posts with lastPostId: ' + lastPostId);

        const controller = new AbortController();

        fetch(`${M.cfg.wwwroot}/local/learningcompanions/ajax_newmessages.php?groupId=${activeGroupid}&lastPostId=${lastPostId}`, {
            signal: controller.signal
        })
        .then(response => response.json())
        .then(data => {
            console.log('New Posts:', data);
            const newPosts = Object.values(data.posts).reverse();

            if (newPosts.length) {
                setPosts((posts) => [...newPosts, ...posts]);
                setLastPostId(newPosts[0].id);
            }
        });

        return () => controller.abort();
    }

    useEffect(getMorePosts, [page]);
    useEffect(getInitialPosts, [activeGroupid, reload]);
    useEffect(getNewPosts, [chattimer]);

    //Wrap the setInterval in a useEffect hook, so it doesn´t add a new interval on every render.
    useEffect(() => {
        const intervalId = window.setInterval(() => {
            setChattimer(chattimer + 1);
        }, 10000);
        console.log('Adding Interval', intervalId);
        return () => {
            window.clearInterval(intervalId);
        }
    }, []);


    const handleWrapperScroll = (e) => {
        if (-e.target.scrollTop + e.target.clientHeight >= (e.target.scrollHeight)) {
            setPage((page) => page + 1);
        }
    };

    return (
        <div id="learningcompanions_chat-postlist">
            <LoadingIndicator loading={loading} />
            <GroupHeader group={group}/>
            <div className="post-wrapper" onScroll={handleWrapperScroll}>
                {posts.map(post => {
                        return (
                            <Post author={post.author} key={post.id} id={post.id} datetime={post.datetime}
                                  comment={post.comment} attachments={post.attachments}/>
                        );
                    }
                )}
            </div>
        </div>
    );
};
