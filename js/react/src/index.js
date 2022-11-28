import React from 'react';
import ReactDOM from 'react-dom/client';
import './index.css';
import App from './App';
import reportWebVitals from './reportWebVitals';
const posts = [];
const groups = [];

const root1 = ReactDOM.createRoot(document.getElementById('learningcompanions_groups-content'));
const root2 = ReactDOM.createRoot(document.getElementById('learningcompanions_chat-content'));

root1.render(
  <React.StrictMode>
    <App posts={posts} groups={groups} component="groups"/>
  </React.StrictMode>
);
root2.render(
    <React.StrictMode>
        <App posts={posts} groups={groups} component="chat"/>
    </React.StrictMode>
);

// If you want to start measuring performance in your app, pass a function
// to log results (for example: reportWebVitals(console.log))
// or send to an analytics endpoint. Learn more: https://bit.ly/CRA-vitals
reportWebVitals();
