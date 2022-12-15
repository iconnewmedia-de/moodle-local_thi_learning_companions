/* eslint-disable no-console */
import $ from 'jquery';
import {getInvitableUsers, inviteUser} from "local_learningcompanions/repository";
import Template from 'core/templates';
import KeyCodes from 'core/key_codes';

export const init = () => {
    $('.js-invite-member-search-btn').click(handleSearchInviteButton);
    $('.js-invite-member-input').keyup(handleSearchKeyup);
    $('.js-invite-member-btn').click(handleInviteSubmit);
};

const handleSearchInviteButton = async function(e) {
    e.preventDefault();

    const groupId = $(this).data('groupid');
    const searchString = $('.js-invite-member-input').val();

    const users = await getInvitableUsers(searchString, groupId);

    const {html} = await Template.renderForPromise('local_learningcompanions/group/group_invite_list', {
        users
    });

    $('.js-invite-member-list').html(html);
};

const handleSearchKeyup = function(e) {
    if (e.keyCode === KeyCodes.enter) {
        handleSearchInviteButton.bind($('.js-invite-member-search-btn'))(e);
    }
};

const handleInviteSubmit = async function(e) {
    e.preventDefault();

    const groupId = $(this).data('groupid');
    const userId = +$('input.js-invite-radio[type=radio]:checked').val();

    const {errorcode} = await inviteUser(userId, groupId);

    if (errorcode === 0) {
        window.location.reload();
    } else {
        console.log(errorcode);
    }
};
