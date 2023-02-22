/* eslint-disable max-len, jsdoc/require-jsdoc, require-jsdoc */
define([
    'jquery',
    'core/config',
    'core/str',
    'core/modal_factory',
    'core/modal_events',
    'local_learningcompanions/datatables-helpers',
    // 'local_learningcompanions/jquery.dataTables',
    'local_learningcompanions/datatables',
    'local_learningcompanions/select2'
], function($, c, str, ModalFactory, ModalEvents, datatablesHelpers) {
    async function initDatatables() {
        datatablesHelpers.initMinSearch('.js-mentor-filter--min');
        datatablesHelpers.initIncludeSearch('.js-mentor-filter--includes');

        const languageUrl = await str.get_string('datatables_url', 'local_learningcompanions');

        $('#askedquestionstable').DataTable({
            language: {
                url: languageUrl
            },
            initComplete: function() {
                datatablesHelpers.makeTablesFullWidth();
            }
        });
        $('#mymentorquestionstable').DataTable({
            language: {
                url: languageUrl
            },
            initComplete: function() {
                datatablesHelpers.makeTablesFullWidth();
            }
        });
        $('#allmentorquestionstable').DataTable({
            language: {
                url: languageUrl
            },
            initComplete: function() {
                datatablesHelpers.makeTablesFullWidth();
            }
        });
        $('#allmentorstable').DataTable({
            dom: 'lrtip',
            language: {
                url: languageUrl,
            },
            initComplete: function() {
                const table = this.api();

                datatablesHelpers.setupSearchRules('.js-mentor-filter--search', table);

                datatablesHelpers.addRedrawEvent('.js-mentor-filter', table);
                datatablesHelpers.makeTablesFullWidth();
            },
            columnDefs: [
                {orderable: false, visible: false, targets: 'no-show'}
            ]
        });
        $('.lc_datatable').DataTable({
            language: {
                url: languageUrl
            },
            initComplete: function() {
                datatablesHelpers.makeTablesFullWidth();
            }
        });
    }

    return {

        select2: function() {
            $('.select2').select2();
        },

        init: function() {
            initDatatables();

            $('.askedquestions-delete').click(function(e) {
                e.preventDefault();

                const questionid = $(this).data('qid');
                const question = $(this).data('question');

                var strings = [
                    {key: 'modal-deletemyquestion-title', component: 'local_learningcompanions'},
                    {key: 'modal-deletemyquestion-text', component: 'local_learningcompanions', param: question},
                    {key: 'modal-deletemyquestion-okaybutton', component: 'local_learningcompanions'},
                    {key: 'modal-deletemyquestion-cancelbutton', component: 'local_learningcompanions'},
                ];

                str.get_strings(strings).then(function(strings) {
                    return ModalFactory.create({
                        title: strings[0],
                        body: '' +
                            '<div id="mentor-deletemyquestion-modal">' +
                            '<div id="mentor-deletemyquestion-modal-text">' + strings[1] + '</div>' +
                            '</div>',
                        footer: '' +
                            '<div id="mentor-deletemyquestion-modal-buttons">' +
                            '<button class="btn btn-primary" aria-hidden="true" id="mentor-deletemyquestion-modal-delete" data-qid="' + questionid + '">' + strings[2] + '</button>' +
                            '<button class="btn btn-secondary" aria-hidden="true" id="mentor-deletemyquestion-modal-close" data-action="hide">' + strings[3] + '</button>' +
                            '</div>'
                    });
                }).then(function(modal) {
                    modal.getRoot().on(ModalEvents.hidden, function() {
                        modal.destroy();
                    });
                    modal.show();
                });
            });

            $('body').on('click', '#mentor-deletemyquestion-modal-delete', function() {

                const questionid = $(this).data('qid');

                $.ajax({
                    url: M.cfg.wwwroot + '/local/learningcompanions/ajax/ajax.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'deletemyquestion',
                        questionid: questionid
                    },
                    success: function (data) {
                        if (data === 'fail') {
                            window.location.href = (M.cfg.wwwroot + '/local/learningcompanions/mentor/index.php?n=n_d');
                        } else {
                            window.location.href = (M.cfg.wwwroot + '/local/learningcompanions/mentor/index.php?n=d');
                        }
                    }
                });
            });

            $('body').on('click', '#mentor-deletemyquestion-modal-close', function() {
                $('.modal').remove();
            });
        }

    };
});
