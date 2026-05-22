import { reactive, computed } from 'vue';

const state = reactive({
    cart: [],
    wishlist: [1, 2],
});

export function useStore() {
    const cartTotal = computed(() =>
        state.cart.reduce((sum, item) => sum + item.product.price * item.qty, 0),
    );

    const cartCount = computed(() =>
        state.cart.reduce((sum, item) => sum + item.qty, 0),
    );

    function findCartItem(productId) {
        return state.cart.find((item) => item.product.id === productId);
    }

    function addToCart(product, qty = 1) {
        const existing = findCartItem(product.id);
        if (existing) {
            existing.qty += qty;
        } else {
            state.cart.push({ product, qty });
        }
    }

    function removeFromCart(productId) {
        const index = state.cart.findIndex((item) => item.product.id === productId);
        if (index > -1) {
            state.cart.splice(index, 1);
        }
    }

    function updateQty(productId, qty) {
        const item = findCartItem(productId);
        if (!item) return;
        if (qty <= 0) {
            removeFromCart(productId);
        } else {
            item.qty = qty;
        }
    }

    function clearCart() {
        state.cart.splice(0, state.cart.length);
    }

    function isInWishlist(productId) {
        return state.wishlist.includes(productId);
    }

    function toggleWishlist(productId) {
        const index = state.wishlist.indexOf(productId);
        if (index > -1) {
            state.wishlist.splice(index, 1);
        } else {
            state.wishlist.push(productId);
        }
    }

    return {
        cart: state.cart,
        wishlist: state.wishlist,
        cartTotal,
        cartCount,
        addToCart,
        removeFromCart,
        updateQty,
        clearCart,
        isInWishlist,
        toggleWishlist,
    };
}
