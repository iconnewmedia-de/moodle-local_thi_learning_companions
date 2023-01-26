import {useGetString} from "../../hooks/moodleHelpers.js";

export default function DeleteButton({id}) {
    const title = useGetString('delete_post');
    console.log('Delete Button:', title);
    return <a href='#' data-id={id} title={title} className='learningcompanions_delete_comment'></a>;
}
