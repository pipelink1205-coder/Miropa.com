export function whenEchoReady(callback, maxWaitMs = 15000) {
    if (window.Echo) {
        callback(window.Echo);
        return () => {};
    }

    const interval = setInterval(() => {
        if (window.Echo) {
            clearInterval(interval);
            clearTimeout(timeout);
            callback(window.Echo);
        }
    }, 200);

    const timeout = setTimeout(() => clearInterval(interval), maxWaitMs);

    return () => {
        clearInterval(interval);
        clearTimeout(timeout);
    };
}

export function dispatchChatMessage(event) {
    window.dispatchEvent(new CustomEvent('chat:message-received', { detail: event }));
}
