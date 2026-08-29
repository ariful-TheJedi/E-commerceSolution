import { mountProductDemoList } from './ProductDemoList';

function mountIslands(): void {
    document.querySelectorAll<HTMLElement>('[data-island]').forEach((el) => {
        const name = el.dataset.island;
        if (name === 'product-demo-list') {
            mountProductDemoList(el);
        }
    });
}

mountIslands();
