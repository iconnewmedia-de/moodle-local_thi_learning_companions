define(['jquery', 'core/str'], function($, Str){
    return {
        init: function(bgcolor, textcolor, borderradius) {
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
                $('.activityinstance, .activity-item').append(
                    '<div class="learningcompanions-group-me-up" ' +
                    'style="background-color:' + bgcolor + ';' +
                    'color:' + textcolor + ';' +
                    'border-radius: ' + borderradius + 'px">' + strings[0] + '</div>');
            });
        }
    };
});
