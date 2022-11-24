define([
    'jquery',
    'core/config',
    'core/str',
    'core/modal_factory',
    'core/modal_events',
    'local_learningcompanions/datatables-helpers',
    'local_learningcompanions/jquery.dataTables',
    'local_learningcompanions/select2'
], function($, c, str, ModalFactory, ModalEvents, datatablesHelpers) {
    return {

        select2: function() {
            $('.select2').select2();
        },

        init: function() {
            datatablesHelpers.initMinSearch('.js-group-filter--min');
            datatablesHelpers.initIncludeSearch('.js-group-filter--includes');

            var translationURL = str.get_string('datatables_url', 'local_learningcompanions');

            // eslint-disable-next-line promise/catch-or-return,promise/always-return
            translationURL.then(function(url) {
                $('#allgroupstable').DataTable({
                    dom: 'lrtip',
                    language: {
                        url: url
                    },
                    initComplete: function() {
                        const table = this.api();

                        datatablesHelpers.setupSearchRules('.js-group-filter--search', table);

                        datatablesHelpers.addRedrawEvent('.js-group-filter', table);
                    },
                });
            });

            $('.grouprow').click(function(e) {
                e.preventDefault();

                const groupid = $(this).data('gid');
                const groupname = $(this).data('title');

                $.ajax({
                    url: M.cfg.wwwroot + '/local/learningcompanions/ajax.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'getgroupdetails',
                        groupid: groupid
                    },
                    success: function(data) {

                        var strings = [
                            {key: 'modal-groupdetails-groupname', component: 'local_learningcompanions', param: groupname}
                        ];

                        str.get_strings(strings).then(function(strings) {
                            return ModalFactory.create({
                                title: strings[0],
                                body: data,
                                footer: '',
                                large: true
                            });
                        }).then(function(modal) {
                            modal.getRoot().on(ModalEvents.hidden, function() {
                                modal.destroy();
                            });
                            modal.show();
                        });
                    }
                });

            });

            $('body').on('click', '#mentor-deletemyquestion-modal-close', function() {
                $('.modal').remove();
            });
        }

    };
});
