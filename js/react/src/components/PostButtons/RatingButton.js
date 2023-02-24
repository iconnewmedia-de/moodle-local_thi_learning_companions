export default function RatingButton({id, isratedbyuser}) {
    return <a href='#' data-id={id} title="Rate" className={`learningcompanions_rate_comment ${isratedbyuser ? "learningcompanions_israted_by_current_user" : ""}`}></a>;
}
