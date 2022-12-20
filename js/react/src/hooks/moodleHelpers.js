/**
 *
 * @param key {string}
 * @param component {string}
 * @param params {string|Object}
 * @param lang {?string}
 * @returns {string}
 */
export const useGetString = async (key, component = 'core', params = [], lang) => {
    lang = lang || navigator.language || navigator.userLanguage;
    const strings = await fetchStrings([{key, component, params, lang}]);
    return strings[0].data;
};

/**
 *
 * @param requests {{key: string, component: ?string, params: ?string|?object, lang: ?string}[]}
 */
export const useGetStrings = async (requests) => {
    console.log('useGetStrings1: ', requests);
    const formatedRequests = requests.map(request => {
        console.log('Inner request: ', request);
        request.lang = request.lang || navigator.language || navigator.userLanguage;
        request.component = request.component || 'core';
        request.params = request.params || [];
        return request;
    });
    console.log('useGetStrings2:', formatedRequests);
    const strings = await fetchStrings(formatedRequests);

    return strings.map(string => {
        return string.data;
    });
};

/**
 * @param requests {{key: string, component: ?string, params: ?string|?object, lang: ?string}[]}
 * @returns {Promise<{error: bool, data: string}[]>}
 */
const fetchStrings = async (requests) => {
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
