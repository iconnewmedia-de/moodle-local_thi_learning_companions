import React from "react";

export default function GroupHeader({group}) {
    return (
        <div className='learningcompanions_groupheader'>
            <div className='learningcompanions_groupimage' style={{backgroundImage:'url(' + group.imageurl + ')'}}></div><h1>{group.name}</h1><div className='learningcompanions_editgroup'></div>
        </div>
    );
}