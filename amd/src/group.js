define([
    'jquery',
    'core/config',
    'core/str',
    'core/modal_factory',
    'core/modal_events',
    'local_learningcompanions/jquery.dataTables',
    'local_learningcompanions/select2'
], function($, c, str, ModalFactory, ModalEvents) {
    return {

        select2: function() {
            $('.select2').select2();
        },

        init: function() {
            var translationURL = str.get_string('datatables_url', 'local_learningcompanions');
            translationURL.then(function(url) {
                $('#allgroupstable').DataTable({
                    language: {
                        url: url
                    }
                });
            });
        }

    };
});
