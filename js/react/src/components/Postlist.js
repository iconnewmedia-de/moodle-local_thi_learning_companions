import React from "react";
import Post from "./Post";


export default function Postlist({posts}) {
    return (
        <div id="learningcompanions_chat-postlist">
            {posts.map(post => {
                console.log('post:', post);
                return (
                <Post author={post.author} key={post.id} id={post.id} datetime={post.datetime} comment={post.comment} attachments={post.attachments} />
            );
            }
            )}
        </div>
            );
};