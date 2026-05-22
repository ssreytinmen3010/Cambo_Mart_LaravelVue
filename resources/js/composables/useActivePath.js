import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function useActivePath() {
    const page = usePage();

    const pathname = computed(() => {
        const url = page.url ?? '/';
        return url.split('?')[0] || '/';
    });

    function isActive(path) {
        const current = pathname.value;

        if (path === '/') {
            return current === '/' || current === '';
        }

        if (path === '/account' && (current === '/account' || current === '/profile')) {
            return true;
        }

        return current === path || current.startsWith(`${path}/`);
    }

    return { pathname, isActive };
}
