/* eslint no-console: off, no-undef: off, no-unused-vars: off */
// import React from 'react';
import ReactDOM from 'react-dom/client';
import Grouplist from "./components/Grouplist";
import Postlist from "./components/Postlist";

const root1 = ReactDOM.createRoot(document.getElementById('learningcompanions_groups-content'));
const root2 = ReactDOM.createRoot(document.getElementById('learningcompanions_chat-content'));
// if (typeof learningcompanions_groupid === "undefined") {
//     var learningcompanions_groupid = 1;
// }

root1.render(
    <React.StrictMode>
        <Grouplist activeGroupid={window.learningcompanions_groupid}/>
    </React.StrictMode>
);
root2.render(
    <React.StrictMode>
        <Postlist activeGroupid={window.learningcompanions_groupid}/>
    </React.StrictMode>
);
