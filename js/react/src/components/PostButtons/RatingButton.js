export default function RatingButton({id, isratedbyuser}) {
    return <a href='#' data-id={id} title="Rate" className={`thi_learning_companions_rate_comment ${isratedbyuser ? "thi_learning_companions_israted_by_current_user" : ""}`}></a>;
}
