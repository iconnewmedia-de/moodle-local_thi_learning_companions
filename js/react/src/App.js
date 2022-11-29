import './App.css';
import React from "react";
import Postlist from "./components/Postlist";
import Grouplist from "./components/Grouplist";
var icblock_init = false;
function App(props) {
  if (typeof M === "undefined") {
    var M = {cfg: {wwwroot: ''}};
  }
  const [posts, setPosts] = React.useState(props.posts);
  const [groups, setGroups] = React.useState(props.groups);
  const [currentGroup, setCurrentGroup] = React.useState(props.activeGroupid);
  const handleGroupSelect = (groupid) => {
    setCurrentGroup(groupid);
  };
  async function getPosts() {
    const response = await fetch(M.cfg.wwwroot + '/local/learningcompanions/ajaxchat.php?groupid=' + currentGroup);
    const data = await response.json();
    console.log('data.posts:', data.posts);
    icblock_init = true;
    let posts = Object.values(data.posts);
    setPosts(posts);
    window.setTimeout(getPosts, 10000);
  }
  async function getGroups() {
    const groups = await fetch(M.cfg.wwwroot + '/local/learningcompanions/ajaxgrouplist.php');
    const data = await groups.json();
    console.log('data.groups:', data.groups);
    icblock_init = true;
    setGroups(data.groups);
    window.setTimeout(getGroups, 50000);
  }
  if (!icblock_init) {
    if (props.component == "chat") {
      console.log('is component chat');
      getPosts();
    }
    if (props.component == "groups") {
      console.log('is component groups');
      getGroups();
    }
    window.setTimeout(function() {


      // collapse reply form before displaying
      // document.getElementById('id_general').classList.add('collapsed');
      // document.getElementById('id_generalcontainer').classList.remove('show');
      // document.querySelector('#id_general [data-toggle="collapse"]').classList.add('collapsed');
      if (props.component == "chat") {
        const container = document.getElementById('learningcompanions_chat-content');
        console.log('about to scroll:', container, container.scrollHeight);
        document.getElementById('learningcompanions_chat-loading').style.display = 'none';
        document.getElementById('learningcompanions_chat-content').style.display = 'block';
        container.scrollTo({top: container.scrollHeight, behavior: 'smooth'});
      } else if (props.component == "groups") {
        document.getElementById('learningcompanions_groups-loading').style.display = 'none';
        document.getElementById('learningcompanions_groups-content').style.display = 'block';
      }

      // document.querySelector('#learningcompanions_chat form').style.display = 'block';


    }, 1000); // wait for the posts to render, then scroll to the bottom
  }

  return (
      <div>
        {props.component === 'groups' &&
            <Grouplist groups={groups} handleGroupSelect={handleGroupSelect} />
        }
        {props.component === 'chat' &&
            <Postlist posts={posts}/>
        }
      </div>
  );
}

export default App;
