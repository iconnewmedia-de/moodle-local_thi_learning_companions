// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Potential user selector module.
 *
 * @module     local_thi_learning_companions/invitation_potential_user_selector
 * @copyright  2023 ICON Vernetzte Kommunikation GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/templates', 'core/str'], function($, Ajax, Templates, Str) {

    return /** @alias module:local_thi_learning_companions/invitation_potential_user_selector */ {

        processResults: function(selector, results) {
            console.log('called processResults with params:', selector, results);
            var users = [];
            if ($.isArray(results)) {
                $.each(results, function(index, user) {
                    users.push({
                        value: user.id,
                        label: user._label
                    });
                });
                return users;

            } else {
                return results;
            }
        },

        transport: function(selector, query, success, failure) {
            console.log('called transport with params:', selector, query, success, failure);
            var promise;
            var groupid = $(selector).attr('groupid');
            var userfields = ['firstname', 'lastname'];

            var perpage = parseInt($(selector).attr('perpage'));
            if (isNaN(perpage)) {
                perpage = 100;
            }

            promise = Ajax.call([{
                methodname: 'local_thi_learning_companions_get_invitable_users', // see local_thi_learning_companions\external::get_invitable_users
                args: {
                    groupid: groupid,
                    query: query,
                    // searchanywhere: true,
                    // page: 0,
                    // perpage: perpage + 1
                }
            }]);

            promise[0].then(function(results) {
                var promises = [],
                    i = 0;

                if (results.length <= perpage) {
                    // Render the label.
                    const profileRegex = /^profile_field_(.*)$/;
                    $.each(results, function(index, user) {
                        var ctx = user,
                            identity = [];
                        $.each(userfields, function(i, k) {
                            const result = profileRegex.exec(k);
                            if (result) {
                                if (user.customfields) {
                                    user.customfields.forEach(function(customfield) {
                                        if (customfield.shortname === result[1]) {
                                            ctx.hasidentity = true;
                                            identity.push(customfield.value);
                                        }

                                    });
                                }
                            } else {
                                if (typeof user[k] !== 'undefined' && user[k] !== '') {
                                    ctx.hasidentity = true;
                                    identity.push(user[k]);
                                }
                            }
                        });
                        ctx.identity = identity.join(', ');
                        promises.push(Templates.render('local_thi_learning_companions/invitation_suggestion', ctx));
                    });

                    // Apply the label to the results.
                    return $.when.apply($.when, promises).then(function() {
                        var args = arguments;
                        $.each(results, function(index, user) {
                            user._label = args[i];
                            i++;
                        });
                        success(results);
                        return;
                    });

                } else {
                    return Str.get_string('toomanyuserstoshow', 'core', '>' + perpage).then(function(toomanyuserstoshow) {
                        success(toomanyuserstoshow);
                        return;
                    });
                }

            }).fail(failure);
        }

    };

});
