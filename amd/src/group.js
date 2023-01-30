/* eslint-disable jsdoc/require-jsdoc, no-console, max-len */
import $ from 'jquery';
import * as str from 'core/str';
import * as ModalFactory from 'core/modal_factory';
import * as ModalEvents from 'core/modal_events';
import * as datatablesHelpers from 'local_learningcompanions/datatables-helpers';
import {promiseAjax} from "local_learningcompanions/ajax";
import 'local_learningcompanions/jquery.dataTables';
import 'local_learningcompanions/select2';
import DynamicForm from 'core_form/dynamicform';
import Templates from 'core/templates';
import {init as inviteInit} from 'local_learningcompanions/invite_members';

export const select2 = () => {
    $('.select2').select2();
};

export const init = () => {
    console.log('init');
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

            // trigger onchange event for course name filter upon page load in case we prefilter by course name
            var theinput = document.querySelector('input[data-target="course"]');
            theinput.dispatchEvent(new Event('change'));
        },
    });


};

const attachEvents = () => {
    const body = $('body');
    $('.grouprow').click(handleTableRowClick);

    body.on('click', '#mentor-deletemyquestion-modal-close', () => $('.modal').remove());

    body.on('click', '.js-leave-group', handleGroupLeaveButton);
    body.on('click', '.js-request-join-group', handleGroupRequestButton);
    body.on('click', '.js-invite-member', handleGroupInviteButton);
};

const handleTableRowClick = async function(e) {
    e.preventDefault();

    const groupid = $(this).data('gid');
    const groupname = $(this).data('title');

    const groupDetailsPromise = promiseAjax(M.cfg.wwwroot + '/local/learningcompanions/ajax/ajax.php', {
        action: 'getgroupdetails',
        groupid: groupid
    });
    const titlePromise = str.get_string('modal-groupdetails-groupname', 'local_learningcompanions', groupname);

    const [{html}, title] = await Promise.all([groupDetailsPromise, titlePromise]);

    const modal = await ModalFactory.create({
        title: title,
        body: html,
        footer: '',
        large: true
    });

    modal.getRoot().on(ModalEvents.hidden, function() {
        modal.destroy();
    });
    modal.show();
};

export const handleGroupInviteButton = async function(e) {
    e.preventDefault();

    const groupId = $(this).data('groupid');

    const templatePromise = Templates.renderForPromise('local_learningcompanions/group/group_invite', {
        groupId
    });
    const titlePromise = str.get_string('group_invite_title', 'local_learningcompanions');

    const [{html}, title] = await Promise.all([templatePromise, titlePromise]);

    const modal = await ModalFactory.create({
        title: title,
        body: html,
        footer: '',
        large: false
    });

    modal.getRoot().on(ModalEvents.hidden, function() {
        modal.destroy();
    });
    modal.show();

    inviteInit();
};

export const handleGroupLeaveButton = async function(e) {
    e.preventDefault();

    const groupId = $(this).data('groupid');

    /**
     * @type {{needsNewAdmin: ?bool, leaved: bool, isLastMember: ?bool}}
     */
    const response = await promiseAjax(M.cfg.wwwroot + '/local/learningcompanions/ajax/ajax.php', {
        action: 'leavegroup',
        groupid: groupId
    });

    if (response.needsNewAdmin) {
        const possibleNewAdminsBodyPromise = promiseAjax(M.cfg.wwwroot + '/local/learningcompanions/ajax/ajax.php', {
            action: 'getpossiblenewadmins',
            groupid: groupId
        });
        const titlePromise = str.get_string('modal-groupdetails-needsnewadmin', 'local_learningcompanions');

        const [possibleNewAdminsBody, title] = await Promise.all([possibleNewAdminsBodyPromise, titlePromise]);

        const modal = await ModalFactory.create({
            title: title,
            body: possibleNewAdminsBody,
            footer: '',
            large: false
        });

        modal.getRoot().on(ModalEvents.hidden, function() {
            modal.destroy();
        });
        modal.show();

        const newAdminForm = new DynamicForm(document.querySelector('#formcontainer'), 'local_learningcompanions\\forms\\assign_new_admin_while_leaving_form');
        newAdminForm.load({groupId: groupId});
        newAdminForm.addEventListener(newAdminForm.events.FORM_SUBMITTED, () => {
            window.location.reload();
        });
    }

    if (response.isLastMember) {
        const templatePromise = Templates.renderForPromise('local_learningcompanions/group/group_modal_confirm_leave', {});
        const titlePromise = str.get_string('modal-groupdetails-leavetitle', 'local_learningcompanions');

        const [{html}, title] = await Promise.all([templatePromise, titlePromise]);

        const modal = await ModalFactory.create({
            title: title,
            body: html,
            footer: '',
            large: false
        });

        modal.getRoot().on(ModalEvents.hidden, function() {
            modal.destroy();
        });
        modal.show();

        const confirmLeaveForm = new DynamicForm(document.querySelector('#formcontainer'), 'local_learningcompanions\\forms\\last_user_leaves_closed_group_form');
        confirmLeaveForm.load({groupId: groupId});
        confirmLeaveForm.addEventListener(confirmLeaveForm.events.FORM_SUBMITTED, () => {
            window.location.reload();
        });
        confirmLeaveForm.addEventListener(confirmLeaveForm.events.FORM_CANCELLED, () => {
            modal.destroy();
        });
    }

    if (response.leaved) {
        window.location.reload();
    }
};

export const handleGroupRequestButton = async function(e) {
    e.preventDefault();

    const error = await promiseAjax(M.cfg.wwwroot + '/local/learningcompanions/ajax/ajax.php', {
        action: 'requestgroupjoin',
        groupid: $(this).data('groupid')
    });

    if (!error) {
        window.location.reload();
    } else {
        const errorMessage = await str.get_string('group_request_not_possible', 'local_learningcompanions');
        alert(errorMessage);
    }
};
