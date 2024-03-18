import {useGetString} from "../../hooks/moodleHelpers.js";

export default function DeleteButton({id}) {
    const title = useGetString('delete_post');
    return <a href='#' data-id={id} title={title} className='thi_learning_companions_delete_comment'></a>;
}
