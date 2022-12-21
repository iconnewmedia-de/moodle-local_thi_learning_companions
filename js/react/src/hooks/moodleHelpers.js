/**
 *
 * @param key {string}
 * @param component {string}
 * @param params {string|Object}
 * @param lang {?string}
 * @returns {Promise<string>}
 */
export const useGetString = async (key, component = 'core', params = [], lang) => {
    const s = await useGetStrings([{key, component, params, lang}]);
    return s[0];
};

/**
 *
 * @param requests {{key: string, component?: string, params?: string|object, lang?: string}[]}
 * @returns {Promise<string[]>}
 */
export const useGetStrings = async (requests) => {
    const formatedRequests = requests.map(request => {
        request.lang = request.lang || navigator.language || navigator.userLanguage;
        request.component = request.component || 'core';
        request.params = request.params || [];
        return request;
    });

    const strings = await fetchStrings(formatedRequests);

    return strings.map(string => {
        return string.data;
    });
};

/**
 * @param requests {{key: string, component: ?string, params: ?string|?object, lang: ?string}[]}
 * @returns {Promise<{error: bool, data: string}[]>}
 */
const fetchStrings = (requests) => {
    const data = requests.map(({key,params, ...rest}, index) => {
        return {
            index,
            methodname: 'core_get_string',
            args: {
                stringid: key,
                stringparams: params,
                ...rest
            }
        }
    });

    return fetch('/lib/ajax/service-nologin.php', {
        method: 'POST',
        body: JSON.stringify(data),
    }).then(response => {
        return response.json();
    });
}
