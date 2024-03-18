/* eslint no-console: off, no-undef: off, no-unused-vars: off */
import ReactDOM from 'react-dom/client';
import Grouplist from "./components/Grouplist";
import Postlist from "./components/Postlist.js";

function startChat() {
    const root = ReactDOM.createRoot(document.getElementById('thi_learning_companions_groups-content'));
    root.render(
        <React.StrictMode>
            <Grouplist activeGroupid={window.thi_learning_companions_groupid}/>
        </React.StrictMode>
    );
}

function startQuestionChat() {
    const root = ReactDOM.createRoot(document.getElementById('thi_learning_companions_chat-content'));
    const questionid = window.thi_learning_companions_questionid;
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
