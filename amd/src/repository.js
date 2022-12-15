/* eslint-disable valid-jsdoc, jsdoc/require-param-type */
// https://moodledev.io/docs/guides/javascript/ajax

import {call} from 'core/ajax';

/**
 *
 * @param query {string} The search query
 * @param groupId {number} The group id
 * @returns {Promise<void>}
 */
export const getInvitableUsers = async(query, groupId) => {
    return call([
        {
            methodname: 'local_learningcompanions_get_invitable_users',
            args: {
                query,
                groupId
            }
        }
    ])[0];
};

export const inviteUser = async(userId, groupId) => {
    return call([
        {
            methodname: 'local_learningcompanions_invite_user',
            args: {
                userId,
                groupId
            }
        }
    ])[0];
};
