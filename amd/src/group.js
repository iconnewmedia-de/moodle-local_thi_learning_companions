/* eslint-disable no-console, max-len */
import $ from 'jquery';
import * as str from 'core/str';
import * as ModalFactory from 'core/modal_factory';
import * as ModalEvents from 'core/modal_events';
import * as datatablesHelpers from 'local_thi_learning_companions/datatables-helpers';
import {promiseAjax} from "local_thi_learning_companions/ajax";
// Import 'local_thi_learning_companions/jquery.dataTables';
import 'local_thi_learning_companions/datatables';
import 'local_thi_learning_companions/select2';
import DynamicForm from 'core_form/dynamicform';
import Templates from 'core/templates';
import Fragment from "core/fragment";
import Pending from "core/pending";
import * as Notification from "core/notification";
import * as Autocomplete from "core/form-autocomplete";

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

    var url = await str.get_string('datatables_url', 'local_thi_learning_companions');
    url = M.cfg.wwwroot + url;

    $('#allgroupstable').DataTable({
        dom: 'lrtip',
        language: {
            url: url
        },
        initComplete: function() {
            const table = this.api();

            datatablesHelpers.setupSearchRules('.js-group-filter--search', table);
            datatablesHelpers.addRedrawEvent('.js-group-filter', table);
            datatablesHelpers.makeTablesFullWidth();
            datatablesHelpers.initOrSearch('.js-group-filter--or-search', table);

            // Trigger onchange event for course name filter upon page load in case we prefilter by course name
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

    const groupDetailsPromise = promiseAjax(M.cfg.wwwroot + '/local/thi_learning_companions/ajax/ajax.php', {
        action: 'getgroupdetails',
        groupid: groupid,
        'referrer': 'groupsearch',
        sesskey: M.cfg.sesskey
    });
    const titlePromise = str.get_string('modal-groupdetails-groupname', 'local_thi_learning_companions', groupname);

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
    const pendingPromise = new Pending('local_thi_learning_companions/group:handleGroupInviteButton');
    const groupId = $(this).data('groupid');

    const templatePromise = Fragment.loadFragment('local_thi_learning_companions', 'invitation_form', groupId, {});
    const stringsPromise = str.get_strings([
        {key: 'group_invite_title', component: 'local_thi_learning_companions'},
        {key: 'group_invite_placeholder', component: 'local_thi_learning_companions'},
        {key: 'group_invite_noselection', component: 'local_thi_learning_companions'},
    ]);

    const [template, strings] = await Promise.all([templatePromise, stringsPromise]);

    const modal = await ModalFactory.create({
        title: strings[0],
        body: template,
        footer: '',
        large: false
    });

    modal.getRoot().on(ModalEvents.hidden, function() {
        modal.destroy();
    });
    modal.show();
    $('#id_cancel').on('click', function(e) {
        e.preventDefault();
        modal.destroy();
        return false;
    });
    Autocomplete.enhance("#id_userlist", true, 'local_thi_learning_companions/invitation_potential_user_selector', strings[1],
        false, true, strings[2], true);
    Promise.all([modal, modal.getBodyPromise()])
        .then(([modal, body]) => {
          console.log('modal:', modal, 'body:', body);

            return modal;
        })
        .then(modal => {
            pendingPromise.resolve();

            return modal;
        })
        .catch (Notification.exception);
};

export const handleGroupLeaveButton = async function(e){
    e.preventDefault();

    const groupId = $(this).data('groupid');

    /**
     * @type {{needsNewAdmin: ?bool, leaved: bool, isLastMember: ?bool}}
     */
    const response = await promiseAjax(M.cfg.wwwroot + '/local/thi_learning_companions/ajax/ajax.php', {
        action: 'leavegroup',
        groupid: groupId,
        sesskey: M.cfg.sesskey
    });

    if (response.needsNewAdmin) {
        const possibleNewAdminsBodyPromise = promiseAjax(M.cfg.wwwroot + '/local/thi_learning_companions/ajax/ajax.php', {
            action: 'getpossiblenewadmins',
            groupid: groupId,
            sesskey: M.cfg.sesskey
        });
        const titlePromise = str.get_string('modal-groupdetails-needsnewadmin', 'local_thi_learning_companions');

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

        const newAdminForm = new DynamicForm(document.querySelector('#formcontainer'), 'local_thi_learning_companions\\forms\\assign_new_admin_while_leaving_form');
        newAdminForm.load({groupId: groupId});
        newAdminForm.addEventListener(newAdminForm.events.FORM_SUBMITTED, () => {
            window.location.reload();
        });
    }

    if (response.isLastMember) {
        const templatePromise = Templates.renderForPromise('local_thi_learning_companions/group/group_modal_confirm_leave', {});
        const titlePromise = str.get_string('modal-groupdetails-leavetitle', 'local_thi_learning_companions');

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

        const confirmLeaveForm = new DynamicForm(document.querySelector('#formcontainer'), 'local_thi_learning_companions\\forms\\last_user_leaves_closed_group_form');
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

    const errorCode = await promiseAjax(M.cfg.wwwroot + '/local/thi_learning_companions/ajax/ajax.php', {
        action: 'requestgroupjoin',
        groupid: $(this).data('groupid'),
        sesskey: M.cfg.sesskey
    });

    if (!errorCode) { // All good
        window.location.reload();
    } else { // Error happened
        const errorMessage = await str.get_string(`group_request_error_code_${errorCode}`, 'local_thi_learning_companions');
        Notification.alert(errorMessage);
    }
};
