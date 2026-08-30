function mountIslands(): void {
    document.querySelectorAll<HTMLElement>('[data-island]').forEach((el) => {
        const name = el.dataset.island;
        if (!name) {
            return;
        }
        void name;
    });
}

mountIslands();
