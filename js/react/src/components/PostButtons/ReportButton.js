import {useGetString} from "../../hooks/moodleHelpers.js";

export default function ReportButton({id}) {
    const title = useGetString('report_post');
    return <a href='#' data-id={id} title={title} className='thi_learning_companions_report_comment'></a>;
}
