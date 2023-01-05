/* eslint-disable no-undef, no-console */
import {useState, useEffect, useCallback} from "react";
import Post from "./Post";
import GroupHeader from "./GroupHeader";
import LoadingIndicator from "./LoadingIndicator";
import eventBus from "../helpers/EventBus";

export default function Postlist(props) {
    if (typeof window.M === "undefined") {
        window.M = {cfg: {wwwroot: ''}};
    }

    const [posts, setPosts] = useState([]);
    const [group, setGroup] = useState({});
    const [page, setPage] = useState(1);
    const [postsOffset, setPostsOffset] = useState(0);
    const [activeGroupid, setActiveGroupid] = useState(props.activeGroupid);
    const [chattimer, setChattimer] = useState(0);
    const [isLoading, setIsLoading] = useState(true);
    const [lastPostId, setLastPostId] = useState(null);
    let updateRunning = false;

    // Create them using useCallback, so we dont have to recreate them on every render.
    const handleGroupChanged = useCallback((data) => {
        setActiveGroupid(data.groupid);
        setPage(1);
        setPostsOffset(0);
    }, []);
    const handlePostDeleted = useCallback(({postid}) => {
        setPosts(oldPosts => oldPosts.map(post => {
            if (+post.id === +postid) {
                post.timedeleted = 1;
            }
            return post;
        }));
    }, []);
    const hanldelPostReported = useCallback(({postid}) => {
        setPosts(oldPosts => oldPosts.map(post => {
            if (+post.id === +postid) {
                post.flagged = true;
            }
            return post;
        }));
    }, []);

    useEffect(() => {
        eventBus.on(eventBus.events.GROUP_CHANGED, handleGroupChanged);
        eventBus.on(eventBus.events.MESSAGE_DELETED, handlePostDeleted);
        eventBus.on(eventBus.events.MESSAGE_REPORTED, hanldelPostReported);

        // ICTODO: This doesn´t remove the event listeners.
        return () => {
            eventBus.off(eventBus.events.GROUP_CHANGED, handleGroupChanged);
            eventBus.off(eventBus.events.MESSAGE_DELETED, handlePostDeleted);
            eventBus.off(eventBus.events.MESSAGE_REPORTED, hanldelPostReported);
        }
    }, []);

    function getMorePosts() {
        //If this is the first page, we don´t need to load more posts.
        if (page === 1) {
            setPage(page + 1);
            return;
        }

        if (updateRunning) {
            return;
        }

        updateRunning = true;

        const controller = new AbortController();

        fetch(`${M.cfg.wwwroot}/local/learningcompanions/ajaxchat.php?`+ new URLSearchParams({
            groupid: activeGroupid,
            page: page,
            offset: postsOffset
        }), {
            signal: controller.signal
        })
        .then(response => response.json())
        .then(data => {
            const newPosts = Object.values(data.posts).reverse();
            setPosts((posts) => [...posts, ...newPosts]);
            setPage(page + 1);
        }).catch(error => {
            if (error.name !== 'AbortError') {
                console.log(error);
            }
        }).finally(() => {
            updateRunning = false;
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
            setIsLoading(false);

            // Get the ID of the last element, so we know where to start from when we get new posts.
            setLastPostId(initialPosts[0]?.id ?? 0);
        }).catch(error => {
            if (error.name !== 'AbortError') {
                console.log(error);
            }
        });

        return () => controller.abort();
    };

    const getNewPosts = () => {
        if (lastPostId === null) return;

        const controller = new AbortController();

        fetch(`${M.cfg.wwwroot}/local/learningcompanions/ajax_newmessages.php?groupId=${activeGroupid}&lastPostId=${lastPostId}`, {
            signal: controller.signal
        })
        .then(response => response.json())
        .then(data => {
            const newPosts = Object.values(data.posts).reverse();

            if (newPosts.length) {
                setPostsOffset(postsOffset => postsOffset + newPosts.length);
                setPosts((posts) => [...newPosts, ...posts]);
                setLastPostId(newPosts[0].id);
            }
        });

        return () => controller.abort();
    }

    useEffect(getInitialPosts, [activeGroupid]);
    useEffect(getNewPosts, [chattimer]);

    //Wrap the setInterval in a useEffect hook, so it doesn´t add a new interval on every render.
    useEffect(() => {
        const intervalId = window.setInterval(() => {
            setChattimer((chattimer) => chattimer + 1);
        }, 30000);

        return () => {
            window.clearInterval(intervalId);
        }
    }, []);

    const handleWrapperScroll = (e) => {
        if (-e.target.scrollTop + e.target.clientHeight >= (e.target.scrollHeight)) {
            getMorePosts();
        }
    };

    return (
        <div id="learningcompanions_chat-postlist">
            {isLoading && <LoadingIndicator/>}
            <GroupHeader group={group}/>
            <div className="post-wrapper" onScroll={handleWrapperScroll}>
                {posts.map(post => {
                        return (
                            <Post author={post.author} key={post.id} id={post.id} datetime={post.datetime}
                                  comment={post.comment} attachments={post.attachments} reported={!!+post.flagged}
                                  deleted={!!+post.timedeleted}/>
                        );
                    }
                )}
            </div>
        </div>
    );
};
