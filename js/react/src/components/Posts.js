import Post from "./Post.js";
import {useGetString} from "../hooks/moodleHelpers.js";

export default function Posts({posts, highlightedPostId, isInPreviewMode, handleWrapperScroll, questionid}) {
    if (typeof posts === "undefined" || !posts.length) {
        const noPosts = useGetString('no_posts_available');
        return <div className="post-wrapper p-3">{noPosts}</div>;
    }

    return (
        <div className="post-wrapper" onScroll={handleWrapperScroll}>
            {posts.map(post => <Post post={post} key={post.id} isPreview={isInPreviewMode}
                                     highlighted={post.id === highlightedPostId} questionid={questionid}/>)}
        </div>
    );
}
