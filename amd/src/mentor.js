define([
    'jquery',
    'core/config',
    'core/str',
    'core/modal_factory',
    'local_learningcompanions/jquery.dataTables',
    'local_learningcompanions/select2'
], function($, c, str, ModalFactory) {
    return {

        select2: function() {
            $('.select2').select2();
        },

        init: function() {
            var translationURL = str.get_string('datatables_url', 'local_learningcompanions');
            translationURL.then(function(url) {
                $('#askedquestionstable').DataTable({
                    language: {
                        url: url
                    }
                });
                $('#mymentorquestionstable').DataTable({
                    language: {
                        url: url
                    }
                });
                $('#allmentorquestionstable').DataTable({
                    language: {
                        url: url
                    }
                });
            });
        }

    };
});
