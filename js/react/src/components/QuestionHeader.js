import {useEffect, useState} from "react";

export default function QuestionHeader({questionid}) {
    // console.log('create questionheader for questionid: ', questionid);
    if (questionid === undefined) return;
    const [question, setQuestion] = useState({
        id: questionid, askedby: 0, mentorid: 0, question: "", title: "", topic: 0, timecreated: "0", timeclosed: "0"
    });
    function getQuestion() {
        // console.log('inside questionheader, getting question for questionid: ', questionid);
        if (questionid === null) return;

        const controller = new AbortController();

        fetch(`${M.cfg.wwwroot}/local/learningcompanions/ajax/ajax_getquestion.php?questionid=${questionid}`, {
            signal: controller.signal
        })
            .then(response => response.json())
            .then(data => {
                // console.log('reqceived question: ', data);
                // console.log('data.length', data.length);
                if (data) {
                    setQuestion(data);
                }
            });

        return () => controller.abort();
    }
    useEffect(getQuestion, [questionid]);
    // getQuestion();
    return (
        <div className='learningcompanions_questionheader'>
            <h1>{question.title}</h1>
            <p>{question.question}</p>
        </div>
    );
}
