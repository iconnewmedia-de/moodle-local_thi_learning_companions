/* eslint no-console: off, no-undef: off, no-unused-vars: off */
import ReactDOM from 'react-dom/client';
import Grouplist from "./components/Grouplist";

const root = ReactDOM.createRoot(document.getElementById('learningcompanions_groups-content'));

root.render(
    <React.StrictMode>
        <Grouplist activeGroupid={window.learningcompanions_groupid}/>
    </React.StrictMode>
);
