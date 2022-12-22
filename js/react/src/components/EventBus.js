const eventBus = {
    on(event, callback) {
        document.addEventListener(event, (e) => callback(e.detail));
    },
    dispatch(event, data) {
        document.dispatchEvent(new CustomEvent(event, { detail: data }));

    },
    remove(event, callback) {
        document.removeEventListener(event, callback);
    },
    events: {
        GROUP_CHANGED: 'groupchanged',
        MESSAGE_DELETED: 'learningcompanions_message_deleted',
    }
};
export default eventBus;
