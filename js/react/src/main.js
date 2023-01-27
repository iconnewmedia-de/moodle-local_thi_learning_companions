/* eslint no-console: off, no-undef: off, no-unused-vars: off */
// import React from 'react';
import ReactDOM from 'react-dom/client';
import Grouplist from "./components/Grouplist";

ReactDOM.render(
    <React.StrictMode>
        <Grouplist activeGroupid={window.learningcompanions_groupid}/>
    </React.StrictMode>,
    document.getElementById('learningcompanions_groups-content')
);

