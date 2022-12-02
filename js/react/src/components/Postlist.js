import React from "react";
import Post from "./Post";
import GroupHeader from "./GroupHeader";
import LoadingIndicator from "./LoadingIndicator";
export default function Postlist({posts, group, loading}) {
    return (
        <div id="learningcompanions_chat-postlist">
            <GroupHeader group={group}/>
            <LoadingIndicator loading={loading} />
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