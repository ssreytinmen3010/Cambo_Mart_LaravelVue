import { reactive, computed } from 'vue';

const state = reactive({
    cart: [],
    cartMeta: {
        subtotal: 0,
        discount: 0,
        total_amount: 0,
    },
    wishlist: [],
    cartLoaded: false,
    cartLoading: null,
    wishlistProducts: [],
    wishlistLoaded: false,
    wishlistLoading: null,
    myRatingsByProductId: {},
    currentUserId: null,
});

export function useStore() {
    const cartSubtotal = computed(() => {
        if (state.cartMeta && Number(state.cartMeta.subtotal) >= 0) return Number(state.cartMeta.subtotal);
        return state.cart.reduce((sum, item) => sum + Number(item.price ?? item.product.price) * item.qty, 0);
    });

    const cartDiscount = computed(() => Number(state.cartMeta?.discount ?? 0));

    const cartTotal = computed(() => {
        if (state.cartMeta && Number(state.cartMeta.total_amount) >= 0) return Number(state.cartMeta.total_amount);
        return Math.max(0, cartSubtotal.value - cartDiscount.value);
    });

    const cartCount = computed(() =>
        state.cart.reduce((sum, item) => sum + item.qty, 0),
    );

    function findCartItem(productId) {
        return state.cart.find((item) => item.product.id === productId);
    }

    function applyServerCart(serverCart) {
        const items = serverCart?.items ?? [];
        const productIds = [];

        state.cartMeta.subtotal = Number(serverCart?.subtotal ?? 0);
        state.cartMeta.discount = Number(serverCart?.discount ?? 0);
        state.cartMeta.total_amount = Number(serverCart?.total_amount ?? 0);

        state.cart.splice(0, state.cart.length, ...items.map((item) => ({
            id: item.id,
            product: item.product ? {
                ...item.product,
                code: item.product.product_code ?? item.product.code,
                // Use cart item unit price (already includes promotion discount if any)
                price: Number(item.price ?? item.product.price ?? 0),
                // Pre-discount unit price (price + per-unit discount)
                oldPrice: Number(item.price ?? item.product.price ?? 0) + Number(item.discount_amount ?? 0),
                inStock: Number(item.product.stock ?? 0) > 0,
                badge: item.product.status_stock ?? item.product.badge ?? null,
                promotion: item.product.promotion ?? null,
            } : null,
            qty: Number(item.quantity ?? 0),
            price: Number(item.price ?? item.product?.price ?? 0),
            discountValue: Number(item.discount_value ?? 0),
            discountAmount: Number(item.discount_amount ?? 0),
            discountAmountTotal: Number(item.discount_amount_total ?? 0),
        })));

        for (const item of items) {
            if (item?.product_id) productIds.push(Number(item.product_id));
            else if (item?.product?.id) productIds.push(Number(item.product.id));
        }

        if (productIds.length) loadMyRatings(productIds);
    }

    async function ensureCartLoaded() {
        if (state.cartLoaded) return;
        if (state.cartLoading) return state.cartLoading;

        state.cartLoading = (async () => {
            try {
                const res = await window.axios.get('/user/cart', {
                    headers: { Accept: 'application/json' },
                });
                applyServerCart(res.data?.cart);
                state.cartLoaded = true;
            } finally {
                state.cartLoading = null;
            }
        })();

        return state.cartLoading;
    }

    async function addToCart(product, qty = 1) {
        await ensureCartLoaded();
        const res = await window.axios.post('/user/cart/items', {
            product_id: product.id,
            quantity: qty,
        }, {
            headers: { Accept: 'application/json' },
        });
        applyServerCart(res.data?.cart);
    }

    async function removeFromCart(productId) {
        await ensureCartLoaded();
        const item = findCartItem(productId);
        if (!item?.id) return;

        const res = await window.axios.delete(`/user/cart/items/${item.id}`, {
            headers: { Accept: 'application/json' },
        });
        applyServerCart(res.data?.cart);
    }

    async function updateQty(productId, qty) {
        await ensureCartLoaded();
        const item = findCartItem(productId);
        if (!item?.id) return;

        if (qty <= 0) {
            return removeFromCart(productId);
        }

        const res = await window.axios.put(`/user/cart/items/${item.id}`, { quantity: qty }, {
            headers: { Accept: 'application/json' },
        });
        applyServerCart(res.data?.cart);
    }

    async function clearCart() {
        await ensureCartLoaded();
        const res = await window.axios.delete('/user/cart/clear', {
            headers: { Accept: 'application/json' },
        });
        applyServerCart(res.data?.cart);
    }

    function isInWishlist(productId) {
        return state.wishlist.includes(productId);
    }

    async function ensureWishlistLoaded() {
        if (state.wishlistLoaded) return;
        if (state.wishlistLoading) return state.wishlistLoading;

        state.wishlistLoading = (async () => {
            try {
                const res = await window.axios.get('/user/wishlist', {
                    headers: { Accept: 'application/json' },
                });
                const ids = res.data?.product_ids ?? [];
                const products = res.data?.products ?? [];

                state.wishlist.splice(0, state.wishlist.length, ...ids.map((id) => Number(id)));
                state.wishlistProducts.splice(0, state.wishlistProducts.length, ...products.map((p) => ({
                    id: p.id,
                    name: p.name,
                    image: p.image,
                    code: p.product_code ?? p.code,
                    category: Number(p.category_id ?? 0),
                    brand: Number(p.brand_id ?? 0),
                    price: Number(p.price ?? 0),
                    oldPrice: null,
                    badge: p.status_stock ?? null,
                    inStock: Number(p.stock ?? 0) > 0,
                    rating: Number(p.rating ?? 0),
                    reviews: Number(p.reviews_count ?? 0),
                    createdAt: p.created_at,
                })));

                state.wishlistLoaded = true;
                if (ids.length) loadMyRatings(ids);
            } catch (e) {
                // eslint-disable-next-line no-console
                console.error('Load wishlist failed:', e);
                throw e;
            } finally {
                state.wishlistLoading = null;
            }
        })();

        return state.wishlistLoading;
    }

    async function toggleWishlist(productId) {
        await ensureWishlistLoaded();
        const res = await window.axios.post('/user/wishlist/toggle', { product_id: productId }, {
            headers: { Accept: 'application/json' },
        });

        const ids = res.data?.product_ids ?? [];
        const products = res.data?.products ?? [];
        state.wishlist.splice(0, state.wishlist.length, ...ids.map((id) => Number(id)));
        state.wishlistProducts.splice(0, state.wishlistProducts.length, ...products.map((p) => ({
            id: p.id,
            name: p.name,
            image: p.image,
            code: p.product_code ?? p.code,
            category: Number(p.category_id ?? 0),
            brand: Number(p.brand_id ?? 0),
            price: Number(p.price ?? 0),
            oldPrice: null,
            badge: p.status_stock ?? null,
            inStock: Number(p.stock ?? 0) > 0,
            rating: Number(p.rating ?? 0),
            reviews: Number(p.reviews_count ?? 0),
            createdAt: p.created_at,
        })));
    }

    async function rateProduct(productId, ratingScore) {
        state.myRatingsByProductId[productId] = Number(ratingScore);
        await window.axios.post('/user/reviews', {
            product_id: productId,
            rating_score: ratingScore,
        }, {
            headers: { Accept: 'application/json' },
        });
    }

    async function loadMyRatings(productIds) {
        const uniqueIds = Array.from(new Set((productIds || []).map((id) => Number(id)).filter(Boolean)));
        if (!uniqueIds.length) return;

        try {
            const res = await window.axios.get('/user/reviews/my', {
                params: { product_ids: uniqueIds },
                headers: { Accept: 'application/json' },
            });
            const ratings = res.data?.ratings ?? {};
            for (const [key, value] of Object.entries(ratings)) {
                state.myRatingsByProductId[Number(key)] = Number(value);
            }
        } catch {
            // ignore (not logged in / validation / etc.)
        }
    }

    function setCurrentUser(userId) {
        const normalized = userId == null ? null : Number(userId);
        if (state.currentUserId === normalized) return;

        state.currentUserId = normalized;

        state.cart.splice(0, state.cart.length);
        state.cartMeta.subtotal = 0;
        state.cartMeta.discount = 0;
        state.cartMeta.total_amount = 0;
        state.cartLoaded = false;
        state.cartLoading = null;

        state.wishlist.splice(0, state.wishlist.length);
        state.wishlistProducts.splice(0, state.wishlistProducts.length);
        state.wishlistLoaded = false;
        state.wishlistLoading = null;

        for (const key of Object.keys(state.myRatingsByProductId)) {
            delete state.myRatingsByProductId[key];
        }
    }

    return {
        cart: state.cart,
        cartMeta: state.cartMeta,
        wishlist: state.wishlist,
        wishlistProducts: state.wishlistProducts,
        myRatingsByProductId: state.myRatingsByProductId,
        currentUserId: state.currentUserId,
        cartSubtotal,
        cartDiscount,
        cartTotal,
        cartCount,
        setCurrentUser,
        ensureCartLoaded,
        addToCart,
        removeFromCart,
        updateQty,
        clearCart,
        isInWishlist,
        ensureWishlistLoaded,
        toggleWishlist,
        rateProduct,
        loadMyRatings,
    };
}
