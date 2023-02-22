/* eslint no-console: off, no-undef: off, no-unused-vars: off */
import ReactDOM from 'react-dom/client';
import Grouplist from "./components/Grouplist";
import Postlist from "./components/Postlist.js";

function startChat() {
    const root = ReactDOM.createRoot(document.getElementById('learningcompanions_groups-content'));
    root.render(
        <React.StrictMode>
            <Grouplist activeGroupid={window.learningcompanions_groupid}/>
        </React.StrictMode>
    );
}

function startQuestionChat() {
    const root = ReactDOM.createRoot(document.getElementById('learningcompanions_chat-content'));
    const questionid = window.learningcompanions_questionid;
    root.render(
        <React.StrictMode>
            <Postlist questionid={questionid}/>
        </React.StrictMode>
    );
}

const Chat = {
    startChat,
    startQuestionChat
}

window.Chat = Chat;
