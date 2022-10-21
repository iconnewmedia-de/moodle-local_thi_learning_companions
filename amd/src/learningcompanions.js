/* eslint no-console:off */
define(['jquery', 'core/str'], function($, Str){
    return {
        getcourseid: function() {
            var entries = document.getElementsByTagName('body')[0].classList;
            var myArray = Array.from(entries);
            var courseid = myArray.reduce((a, b) => {
                if (b.indexOf('course-2') === 0) {
                    return b.substr(7);
                }
                return a;
            });
            if (typeof courseid === "undefined" || courseid.length === 0) {
                return false;
            }
            return parseInt(courseid);
        },
        init: function(cssselector, bgcolor, textcolor, borderradius) {
            var courseid = this.getcourseid();
            if (!courseid) {
                return;
            }
            const isColor = (strColor) => {
                const s = new Option().style;
                s.color = strColor;
                return s.color !== '';
            };
            if (!isColor(bgcolor)) {
                bgcolor = '#333';
            }
            if (!isColor(textcolor)) {
                textcolor = '#fff';
            }
            borderradius = parseInt(borderradius);
            Str.get_strings([
                {key: 'group-me-up', component: 'local_learningcompanions'}
            ]).then(function(strings) {
                $(cssselector).append(
                    '<div class="learningcompanions-group-me-up" ' +
                    'style="background-color:' + bgcolor + ';' +
                    'color:' + textcolor + ';' +
                    'border-radius: ' + borderradius + 'px"' +
                    '><a class="learningcompanions-group-me-up-button" href="' +
                    // ICTODO: replace the link below with link to group overview:
                    // list of groups for this course where you can join or create a new group
                    M.cfg.wwwroot + '/local/learningcompanions/creategroup.php?courseid=' + courseid + '&layout=embedded"' +
                    '>' + strings[0] + '</a></div>');
                $('.learningcompanions-group-me-up-button').each((index, element) => {
                    // ICTODO: using the lightbox doesn't work yet
                    console.log('add lightbox to: ', index, element);
                    M.util.add_lightbox(Y, Y.Node(element));
                });
            });
        }
    };
});
