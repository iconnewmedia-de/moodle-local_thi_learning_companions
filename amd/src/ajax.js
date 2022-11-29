import $ from 'jquery';

export const promiseAjax = (url, data, otherOptions = {}) => {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: url,
            method: 'POST',
            dataType: 'json',
            data: data,
            ...otherOptions,
            success: function(data) {
                resolve(data);
            },
            error: function(data) {
                reject(data);
            }
        });
    });
};
