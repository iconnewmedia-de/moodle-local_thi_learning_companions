import Post from "./Post.js";
import {useGetString} from "../hooks/moodleHelpers.js";

export default function Posts({posts, highlightedPostId, isInPreviewMode, handleWrapperScroll, questionid, isDummyGroup = false}) {
    if (isDummyGroup) {
        const notAllowed = useGetString('not_allowed_to_see_posts');
        return <div className="post-wrapper p-3">{notAllowed}</div>;
    }

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
