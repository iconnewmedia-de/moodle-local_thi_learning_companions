export default function GroupHeader({group}) {
    if (group === undefined) return;

    return (
        <div className='thi_learning_companions_groupheader'>
            <div className='thi_learning_companions_groupimage' style={{backgroundImage:'url(' + group.imageurl + ')'}}></div>
            <h1>{group.name}</h1>
            {group.dummyGroup || <a href='#' className='thi_learning_companions_editgroup' data-gid={group.id} data-title={group.name}></a>}
        </div>
    );
}
