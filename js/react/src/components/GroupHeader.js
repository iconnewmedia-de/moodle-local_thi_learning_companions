export default function GroupHeader({group}) {
    return (
        <div className='learningcompanions_groupheader'>
            <div className='learningcompanions_groupimage' style={{backgroundImage:'url(' + group.imageurl + ')'}}></div><h1>{group.name}</h1><a href='#' className='learningcompanions_editgroup' data-gid={group.id} data-title={group.name}></a>
        </div>
    );
}
