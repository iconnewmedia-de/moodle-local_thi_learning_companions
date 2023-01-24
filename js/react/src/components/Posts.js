import Post from "./Post.js";

export default function Posts({posts, highlightedPostId, isInPreviewMode, handleWrapperScroll}) {
    if (!posts.length) {
        return <div className="post-wrapper p-3">[[No Posts available]]</div>;
    }

    return (
        <div className="post-wrapper" onScroll={handleWrapperScroll}>
            {posts.map(post => <Post post={post} key={post.id} isPreview={isInPreviewMode}
                                     highlighted={post.id === highlightedPostId}/>)}
        </div>
    );
}
