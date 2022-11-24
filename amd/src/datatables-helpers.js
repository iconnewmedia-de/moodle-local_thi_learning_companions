/* eslint-disable jsdoc/require-param-type, valid-jsdoc */
import $ from 'jquery';
import 'local_learningcompanions/jquery.dataTables';

/**
 * Adds the elements to the datatables search, for a minimum value search.
 * The elements need a data-target attribute, which is the column index to search.
 *
 * @param selector
 */
export const initMinSearch = (selector) => {
    $(selector).each(function() {
        addMinSearch($(this), $(this).data('target'));
    });
};

/**
 * Adds a default "value is included in the column" search to the datatables search.
 *
 * @param selector {string}
 * @param table {*}
 */
export const setupSearchRules = (selector, table) => {
    $(selector).each(function() {
        $(this).on('keyup change clear', function() {
            let column;
            // Check of the target is a number
            if (isNaN($(this).data('target'))) {
                column = table.column(`.${$(this).data('target')}`);
            } else {
                column = table.column($(this).data('target'));
            }
            column.search(this.value).draw();
        });
    });
};

/**
 * Adds a specific element to the datatables search, for a minimum value search.
 *
 * @param element {jQuery} The element the search relates to.
 * @param targetColumn {int} The column index to search.
 */
export const addMinSearch = (element, targetColumn) => {
    $.fn.dataTable.ext.search.push(function(settings, data) {
        let min = element.val() ?? '';

        if (!min) {
            return true;
        }

        if (element.attr('type') === 'date') {
            const minDate = new Date(min);
            min = minDate.getTime() / 1000;
        }

        if (targetColumn === undefined || isNaN(targetColumn)) {
            // eslint-disable-next-line no-console
            console.error('data-target is not defined or not a number for', element);
            return true;
        }

        /**
         * @type {number}
         */
        const value = +data[targetColumn];
        if (min) {
            return value >= min;
        }
        return true;
    });
};

/**
 * Adds a specific select element to the datatables search, for an include search value search.
 * The element should be an element, that can select multiple values. Otherwise, just use the setupSearchRules function.
 * The elements should have a data-target attribute, which is the column index to search.
 *
 * @param selector {string}
 */
export const initIncludeSearch = (selector) => {
    $(selector).each(function() {
        addIncludeSearch($(this), $(this).data('target'));
    });
};

/**
 * Adds a specific select element to the datatables search, for an include search value search.
 *
 * @param element {jQuery} The element the search relates to.
 * @param targetColumn {int} The column index to search.
 */
export const addIncludeSearch = (element, targetColumn) => {
    $.fn.dataTable.ext.search.push(function(settings, data) {
        /**
         * @type {string[]}
         */
        const include = element.val() ?? [];

        if (targetColumn === undefined || isNaN(targetColumn)) {
            // eslint-disable-next-line no-console
            console.error('data-target is not defined or not a number for', element);
            return true;
        }

        /**
         * @type {string}
         */
        const value = data[targetColumn];

        if (include.length) {
            return include.every((item) => value.includes(item));
        }
        return true;
    });
};

/**
 * This function adds an event listener to the given selector that will redraw the given table
 *
 * @param selector {string}
 * @param table {*}
 */
export const addRedrawEvent = (selector, table) => {
    $(selector).on('change keyup clear', function() {
        table.draw();
    });
};

export const addDateMinSearch = (element, targetColumn) => {
    $.fn.dataTable.ext.search.push(function(settings, data) {
        /**
         * @type {string}
         */
        const minDateString = element.val() ?? '';

        if (!minDateString) {
            return true;
        }

        // MinDate is a string. We need to convert it to a date object and get the timestamp.
        const minDate = (new Date(minDateString)).getTime() / 1000;

        if (targetColumn === undefined || isNaN(targetColumn)) {
            // eslint-disable-next-line no-console
            console.error('data-target is not defined or not a number for', element);
            return true;
        }

        /**
         * @type {int}
         */
        const value = data[targetColumn];

        return value >= minDate;
    });
};