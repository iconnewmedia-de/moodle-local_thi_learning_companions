/* eslint-disable jsdoc/require-param-type */
import $ from 'jquery';
import 'local_thi_learning_companions/datatables';

/**
 * Adds a default "value is included in the column" search to the datatables search.
 *
 * @param selector {string}
 * @param table {*}
 */
export const setupSearchRules = (selector, table) => {
    $(selector).each(function() {
        $(this).on('keyup change clear', function() {
            let elementValue;

            if (this.type === 'checkbox') {
                elementValue = this.checked ? 1 : '';
            } else {
                elementValue = this.value;
            }

            let column;
            // Check of the target is a number
            if (isNaN($(this).data('target'))) {
                column = table.column(`.${$(this).data('target')}`);
            } else {
                column = table.column($(this).data('target'));
            }
            column.search(elementValue).draw();
        });
    });
};

/**
 * Adds the elements to the datatables search, for an OR value search.
 * The elements need a data-target attribute, which is the column index or class to search.
 *
 * @param selector
 */
export const initOrSearch = (selector) => {
    $(selector).each(function() {
        addOrSearch($(this));
    });
};

/**
 * Adds a specific element to the datatables search, for an OR value search.
 *
 * @param element {jQuery} The element the search relates to.
 */
export const addOrSearch = (element) => {
    $.fn.dataTable.ext.search.push((settings, data) => {
        let searchVal = element.val() ?? '';

        // If the search value is empty, don´t filter it.
        if (!searchVal) {
            return true;
        }

        const indexes = getTargetColumnIndexes(settings, element);
        if (indexes === null) {
            return true;
        }

        for (const index of indexes) {
            /**
             * @type {string}
             */
            if (data[index].includes(searchVal)) {
                return true;
            }
        }
        return false;
    });
};

/**
 * Adds the elements to the datatables search, for a minimum value search.
 * The elements need a data-target attribute, which is the column index to search.
 *
 * @param selector
 */
export const initMinSearch = (selector) => {
    $(selector).each(function() {
        addMinSearch($(this));
    });
};

/**
 * Adds a specific element to the datatables search, for a minimum value search.
 *
 * @param element {jQuery} The element the search relates to.
 */
export const addMinSearch = (element) => {
    $.fn.dataTable.ext.search.push((settings, data) => {
        let min = element.val() ?? '';

        if (!min) {
            return true;
        }

        if (element.attr('type') === 'date') {
            const minDate = new Date(min);
            min = minDate.getTime() / 1000;
        }

        let index = getTargetColumnIndexes(settings, element);
        if (index === null) {
            return true;
        }
        index = index[0];

        /**
         * @type {number}
         */
        const value = +data[index];
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
        addIncludeSearch($(this));
    });
};

/**
 * Adds a specific select element to the datatables search, for an include search value search.
 *
 * @param element {jQuery} The element the search relates to.
 */
export const addIncludeSearch = (element) => {
    $.fn.dataTable.ext.search.push((settings, data) => {
        /**
         * @type {string[]}
         */
        const include = element.val() ?? [];

        let index = getTargetColumnIndexes(settings, element);
        if (index === null) {
            return true;
        }
        index = index[0];

        /**
         * @type {string}
         */
        const value = data[index];

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

/**
 *
 * @param settings {*}
 * @param className {string}
 *
 * @returns {null|int}
 */
const getIndexByClass = (settings, className) => {
    for (const index in settings.aoColumns) {
        if (settings.aoColumns[index].nTh.classList.contains(className)) {
            return +index;
        }
    }

    return null;
};

/**
 *
 * @param settings {*}
 * @param element {jQuery}
 * @returns {null|int[]}
 */
const getTargetColumnIndexes = (settings, element) => {
    const targetData = element.data('target');

    // It´s not set? Return null.
    if (targetData === undefined) {
        // eslint-disable-next-line no-console
        console.error('data-target is not defined or not a number for', element);
        return null;
    }

    // If it´s just a single number, return it
    if (!isNaN(targetData)) {
        return [targetData];
    }

    // Split it, so we can support multiple classes.
    const targetArray = targetData.split(',');

    // Map the array to the index.
    return targetArray.map((target) => {
        // It´s a number? Return it.
        if (!isNaN(target)) {
            return target;
        }

        // It´s a string? Try to find the index by the class name.
        return getIndexByClass(settings, target);
    });
};

export const makeTablesFullWidth = function (){
    if (document.querySelector('.dataTable') !== null) {
        // Make page and table full width.
        document.querySelector('body').classList.add('hasDatatable');
        $('.dataTable').css('width', '');
    }
};
