function mountIslands(): void {
    document.querySelectorAll<HTMLElement>('[data-island]').forEach((el) => {
        const name = el.dataset.island;
        if (!name) {
            return;
        }
        // Islands register here as they are added. None yet.
        void name;
    });
}

mountIslands();
