/* eslint-disable no-console */
import {useState, useEffect, useCallback} from "react";
import Posts from "./Posts";
import GroupHeader from "./GroupHeader";
import QuestionHeader from "./QuestionHeader";
import LoadingIndicator from "./LoadingIndicator";
import eventBus from "../helpers/EventBus";

export default function Postlist({activeGroupid, group, questionid}) {
    const [posts, setPosts] = useState([]);
    const [chattimer, setChattimer] = useState(0);
    const [isLoading, setIsLoading] = useState(true);
    const [lastPostId, setLastPostId] = useState(null); //Used, to get only new Posts
    const [firstPostId, setFirstPostId] = useState(null); //Used to get older Posts

    const highlightedPostId = (new URLSearchParams(window.location.search)).get('postId');
    // const group = groups.find(group => +group?.id === +activeGroupid);
    const isInPreviewMode = (!questionid && group)?group.isPreviewGroup:false;
    // console.log('questionid:', questionid);
    // console.log('group:', group);
    // console.log('group?.isPreviewGroup:', group?.isPreviewGroup);
    // console.log('isInPreviewMode:', isInPreviewMode);
    let updateRunning = false;

    useEffect(() => {
        setIsLoading(true);
    }, [activeGroupid]);

    const handlePostDeleted = useCallback(({postid}) => {
        setPosts(oldPosts => oldPosts.map(post => {
            if (+post.id === +postid) {
                post.timedeleted = 1;
            }
            return post;
        }));
    }, []);
    const handlePostReported = useCallback(({postid}) => {
        setPosts(oldPosts => oldPosts.map(post => {
            if (+post.id === +postid) {
                post.flagged = true;
            }
            return post;
        }));
    }, []);
    const handlePostRated = useCallback(({postid, newvalue}) => {
       setPosts(oldPosts => oldPosts.map(post => {
           if (+post.id === +postid) {
               post.isratedbyuser = newvalue;
           }
           return post;
       }));
    });
    const handlePostSend = useCallback(() => {
        setChattimer(chattimer => chattimer + 1);
    }, []);
    const getInitialPosts = useCallback(() => {
        const controller = new AbortController();

        fetch(M.cfg.wwwroot + '/local/learningcompanions/ajax/ajaxchat.php?' + new URLSearchParams({
            groupid: activeGroupid,
            includedPostId: highlightedPostId,
            questionid: questionid,
        }), {
            signal: controller.signal
        })
            .then(response => response.json())
            .then(data => {
                const initialPosts = data.posts;
                setPosts(initialPosts);
                setIsLoading(false);

                // Get the ID of the last element, so we know where to start from when we get new posts.
                setLastPostId(initialPosts[0]?.id ?? 0);
                // Also set the "first" post id, so we can get older posts.
                setFirstPostId(initialPosts[initialPosts.length - 1]?.id ?? Infinity);
            }).catch(error => {
            if (error.name !== 'AbortError') {
                // console.log(error);
            }
        });

        return () => controller.abort();
    }, [activeGroupid, highlightedPostId]);

    useEffect(() => {
        eventBus.on(eventBus.events.MESSAGE_DELETED, handlePostDeleted);
        eventBus.on(eventBus.events.MESSAGE_REPORTED, handlePostReported);
        eventBus.on(eventBus.events.MESSAGE_SEND, handlePostSend);
        eventBus.on(eventBus.events.MESSAGE_RATED, handlePostRated);

        // ICTODO: This doesn´t remove the event listeners.
        return () => {
            eventBus.off(eventBus.events.MESSAGE_DELETED, handlePostDeleted);
            eventBus.off(eventBus.events.MESSAGE_REPORTED, handlePostReported);
            eventBus.off(eventBus.events.MESSAGE_SEND, handlePostSend);
            eventBus.off(eventBus.events.MESSAGE_RATED, handlePostRated);
        }
    }, []);

    // Scroll to the Id
    useEffect(() => {
        if (!highlightedPostId) return;

        const element = document.querySelector(`#learningcompanions_chat-post-${highlightedPostId}`);

        if (!element) {
            return;
        }

        element.scrollIntoView();
    }, [isLoading]);

    function getMorePosts() {
        if (firstPostId === null) return;

        if (updateRunning) return;

        updateRunning = true;

        const controller = new AbortController();

        fetch(`${M.cfg.wwwroot}/local/learningcompanions/ajax/ajaxchat.php?`+ new URLSearchParams({
            groupid: activeGroupid,
            firstPostId: firstPostId,
            questionid: questionid
        }), {
            signal: controller.signal
        })
        .then(response => response.json())
        .then(data => {
            const newPosts = data.posts;

            if (newPosts.length) {
                setPosts((posts) => [...posts, ...newPosts]);
                setFirstPostId(newPosts[newPosts.length - 1].id);
            }
        }).catch(error => {
            if (error.name !== 'AbortError') {
                // console.log(error);
            }
        }).finally(() => {
            updateRunning = false;
        });

        return () => controller.abort();
    }

    function getNewPosts() {
        if (lastPostId === null) return;

        const controller = new AbortController();

        fetch(`${M.cfg.wwwroot}/local/learningcompanions/ajax/ajax_newmessages.php?groupId=${activeGroupid}&lastPostId=${lastPostId}&questionid=${questionid}`, {
            signal: controller.signal
        })
        .then(response => response.json())
        .then(data => {
            const newPosts = data.posts;

            if (newPosts && newPosts.length) {
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
            setChattimer(chattimer => chattimer + 1);
        }, 10000);

        return () => {
            window.clearInterval(intervalId);
        }
    }, []);

    const handleWrapperScroll = (e) => {
        if (-e.target.scrollTop + e.target.clientHeight >= (e.target.scrollHeight)) {
            getMorePosts();
        }
    };
    var header;
    const getHeader = () => {
        if (questionid) {
            // console.log('we have a question id, make a question header. Question ID: ', questionid);
            header = (
                <QuestionHeader questionid={questionid}/>
            );
        } else {
            // console.log('no question id, make a group header. Group: ', group);
            header = (
                <GroupHeader group={group}/>
            );
        }
    };
    useEffect(getHeader, [group, questionid]);
    getHeader();

    return (
        <div id="learningcompanions_chat-postlist">
            {/*<QuestionHeader questionid={questionid}/>*/}
            {header}
            {isInPreviewMode && <span>Is Preview</span>}
            {isLoading && <LoadingIndicator/>}
            {!isLoading && <Posts posts={posts} handleWrapperScroll={handleWrapperScroll} isInPreviewMode={isInPreviewMode} highlightedPostId={highlightedPostId} questionid={questionid} />}
        </div>
    );
};
