window.ChatEngine = {
    channels: {},

    listen(peminjamanId, callback) {
        if (this.channels[peminjamanId]) return;

        const channel = window.Echo
            .private(`chat.${peminjamanId}`)
            .listen('.MessageSent', callback);

        this.channels[peminjamanId] = channel;
    }
};