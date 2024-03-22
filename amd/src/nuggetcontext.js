define(['jquery', 'core/ajax', 'core/notification'], function ($, Ajax, Notification) {
    return {
        /**
         * Source of data for Ajax element.
         *
         * @param {String} selector The selector of the auto complete element.
         * @param {String} query The query string.
         * @param {Function} callback A callback function receiving an array of results.
         * @param {Function} failure A callback function to be called in case of failure, receiving the error message.
         * @return {Void}
         */
        transport: function (selector, query, callback, failure) {
            var courseid = parseInt($('#id_courseid').val());
            if (isNaN(courseid)) {
                courseid = null;
            }
            Ajax.call([{
                methodname: 'local_thi_learning_companions_nugget_list',
                args: {
                    courseid: courseid,
                    query: query
                },
                done: function (data) {
                    // ICTODO: do something
                },
                fail: Notification.exception
            }])[0].then(callback).catch (failure);
        },

        /**
         * Process the results for auto complete elements.
         *
         * @param {String} selector The selector of the auto complete element.
         * @param {Array} results An array or results.
         * @return {Array} New array of results.
         */
        processResults: function (selector, results) {
            var options = [];
            $.each(results, function(index, data) {
                options.push({
                    value: data.id,
                    label: data.name
                });
            });
            return options;
        }
    };
});