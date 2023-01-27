const eventBus = {
    on(event, callback, options = {}) {
        document.addEventListener(event, (e) => callback(e.detail), options);
        return this;
    },
    off(event, callback) {
        return this.remove(event, callback);
    },
    dispatch(event, data) {
        document.dispatchEvent(new CustomEvent(event, { detail: data }));
        return this;
    },
    remove(event, callback) {
        document.removeEventListener(event, callback);
        return this;
    },
    events: {
        MESSAGE_DELETED: 'learningcompanions_message_deleted',
        MESSAGE_REPORTED: 'learningcompanions_message_reported',
    }
};

export default eventBus;
