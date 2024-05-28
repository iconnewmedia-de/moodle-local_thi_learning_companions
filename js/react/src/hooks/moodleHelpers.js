/**
 *
 * @param key {string}
 * @param component {string}
 * @param params {string|Object}
 * @returns {string}
 */
export function useGetString(key, component = 'local_thi_learning_companions', params = []) {
    const targetString = window.M.str[component]?.[key] ?? `[[${key}_not_found]]`;

    //if there are no params, just return the string
    if (!params.length) {
        return targetString;
    }

    //Add params to the $a variables in the string
    const regexWithParam = /{\$a->(\w*)}/gm;
    const regexWithoutParam = /{\$a}/gm;

    //Check if the params are an object
    if (typeof params === 'object') {
        //If so, replace the $a->param with the value
        return targetString.replace(regexWithParam, (match, p1) => {
            return params[p1] ?? match;
        });
    }
    //If not, replace the $a with the param
    return targetString.replace(regexWithoutParam, params);
}

const previewSelector = ".js-chat-preview";
const messageInputSelector = "#fitem_id_message";
const attachmentsSelector = "#fitem_id_attachments";
const requiredHintSelector = '.fdescription.required';
export function useSetChatInput(isPreviewGroup, chatid) {
    let newChatValue = chatid;
    if (isPreviewGroup) {
        newChatValue = '';
        document.querySelector(previewSelector)?.classList.replace('d-none','d-flex');
        document.querySelectorAll(`${messageInputSelector}, ${attachmentsSelector}, ${requiredHintSelector}`).forEach(el => el.classList.add('d-none'));
    } else {
        document.querySelector(previewSelector)?.classList.replace('d-flex', 'd-none');
        document.querySelectorAll(`${messageInputSelector}, ${attachmentsSelector}, ${requiredHintSelector}`).forEach(el => el.classList.remove('d-none'));
    }

    document.querySelector('input[name="chatid"]').value = newChatValue;
}


export function hideForm() {
    const form = document.querySelector('.chat-post-form');
    form.classList.add('d-none');
}
