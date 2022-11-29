import $ from 'jquery';
import * as str from 'core/str';
import * as ModalFactory from 'core/modal_factory';
import * as ModalEvents from 'core/modal_events';
import * as datatablesHelpers from 'local_learningcompanions/datatables-helpers';
import {promiseAjax} from "local_learningcompanions/ajax";
import 'local_learningcompanions/jquery.dataTables';
import 'local_learningcompanions/select2';

export const select2 = () => {
    $('.select2').select2();
};

export const init = () => {
    setupDatatables();
    attachEvents();
};

const setupDatatables = async() => {
    datatablesHelpers.initMinSearch('.js-group-filter--min');
    datatablesHelpers.initIncludeSearch('.js-group-filter--includes');

    const url = await str.get_string('datatables_url', 'local_learningcompanions');

    // eslint-disable-next-line promise/catch-or-return,promise/always-return
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
};

const attachEvents = () => {
    $('.grouprow').click(async function(e) {
        e.preventDefault();

        const groupid = $(this).data('gid');
        const groupname = $(this).data('title');

        const groupDetails = await promiseAjax(M.cfg.wwwroot + '/local/learningcompanions/ajax.php', {
            action: 'getgroupdetails',
            groupid: groupid
        });

        const title = await str.get_string('modal-groupdetails-groupname', 'local_learningcompanions', groupname);
        const modal = await ModalFactory.create({
            title: title,
            body: groupDetails,
            footer: '',
            large: true
        });

        modal.getRoot().on(ModalEvents.hidden, function() {
            modal.destroy();
        });
        modal.show();
    });

    $('body').on('click', '#mentor-deletemyquestion-modal-close', function() {
        $('.modal').remove();
    });
};
