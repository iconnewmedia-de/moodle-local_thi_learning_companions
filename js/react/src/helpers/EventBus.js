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
        MESSAGE_DELETED: 'thi_learning_companions_message_deleted',
        MESSAGE_REPORTED: 'thi_learning_companions_message_reported',
        MESSAGE_SEND: 'thi_learning_companions_message_send',
        MESSAGE_RATED: 'thi_learning_companions_message_rated',
    }
};

export default eventBus;
