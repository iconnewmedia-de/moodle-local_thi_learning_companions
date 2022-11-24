import './App.css';
import React from "react";
import Postlist from "./components/Postlist";
// import Grouplist from "./components/Grouplist";
var icblock_init = false;
function App(props) {
  if (typeof M === "undefined") {
    var M = {cfg: {wwwroot: ''}};
  }
  const [posts, setPosts] = React.useState(props.posts);
  // const [groups, setGroups] = React.useState(props.groups);
  async function getPosts() {
    const response = await fetch(M.cfg.wwwroot + '/local/learningcompanions/ajaxchat.php');
    const data = await response.json();
    console.log('data.posts:', data.posts);
    icblock_init = true;
    let posts = Object.values(data.posts);
    setPosts(posts);
    window.setTimeout(getPosts, 10000);
  }
  // async function getGroups() {
  //   const groups = await fetch(M.cfg.wwwroot + '/local/learningcompanions/ajaxgrouplist.php');
  //   const data = await groups.json();
  //   icblock_init = true;
  //   setGroups(data.groups);
  //   window.setTimeout(getPosts, 50000);
  // }
  if (!icblock_init) {
    getPosts();
    // getGroups();
    window.setTimeout(function() {
      const container = document.getElementById('learningcompanions_chat-content');
      console.log('about to scroll:', container, container.scrollHeight);
      document.getElementById('learningcompanions_chat-loading').style.display = 'none';

      // collapse reply form before displaying
      document.getElementById('id_general').classList.add('collapsed');
      document.getElementById('id_generalcontainer').classList.remove('show');
      document.querySelector('#id_general [data-toggle="collapse"]').classList.add('collapsed');

      document.getElementById('learningcompanions_chat-content').style.display = 'block';
      document.querySelector('#learningcompanions_chat form').style.display = 'block';
      container.scrollTo({top: container.scrollHeight, behavior: 'smooth'});

    }, 1000); // wait for the posts to render, then scroll to the bottom
  }
  return (
      <div>
        {/*<Grouplist groups={groups} />*/}
        <Postlist posts={posts} />
      </div>
  );
}

export default App;
