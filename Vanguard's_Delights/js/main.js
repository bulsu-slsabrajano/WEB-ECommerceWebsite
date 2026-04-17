/* --- SHARED CONFIG --- */
const cartItemsWrapper = document.getElementById('cart-items-wrapper');
const subtotalEl = document.getElementById('cart-subtotal');
const checkoutWrapper = document.getElementById('checkout-items-wrapper');

let cartData = [];

let defaultAddress = window.phpData || {
    name: "Guest User",
    address: "No default address set in database."
};

/* --- CORE DATA FETCHING --- */
async function fetchCartData() {
    try {
        const response = await fetch('../../db/action/togetcart.php');
        if (!response.ok) throw new Error('Database connection failed');
        const data = await response.json();

        return data.map(item => {
            // togetcart.php already normalizes the image path via SQL REPLACE,
            // so we do NOT prefix it manually here to avoid doubling the path.
            return {
                ...item,
                cart_item_id: String(item.cart_item_id),
                checked: true
            };
        });
    } catch (err) {
        console.error("Fetch error:", err);
        return [];
    }
}

/* --- CART PAGE --- */
async function loadCart() {
    if (!cartItemsWrapper) return;
    cartData = await fetchCartData();
    renderCart();
}

function renderCart() {
    if (!cartItemsWrapper) return;
    cartItemsWrapper.innerHTML = '';
    let subtotal = 0;

    if (cartData.length === 0) {
        cartItemsWrapper.innerHTML = '<div class="text-center py-5 text-muted">Your cart is empty</div>';
        if (subtotalEl) subtotalEl.innerText = '₱0.00';
        return;
    }

    cartData.forEach((item, index) => {
        const itemTotal = item.price * item.quantity;
        if (item.checked) subtotal += itemTotal;

        const row = document.createElement('div');
        row.className = 'row cart-row align-items-center mx-0 py-3 border-bottom';
        row.innerHTML = `
            <div class="col-4 d-flex align-items-center ps-5">
                <input type="checkbox" class="form-check-input me-3 cart-checkbox" data-index="${index}" ${item.checked ? 'checked' : ''}>
                <div class="img-container-base"><img src="${item.image_url}" class="product-img"></div>
                <span class="ms-3 fw-bold">${item.name}</span>
            </div>
            <div class="col-2 text-center">₱${parseFloat(item.price).toFixed(2)}</div>
            <div class="col-2 text-center qty-controls">
                <i class="fas fa-minus-circle me-2 cursor-pointer" onclick="changeQty(${index}, -1)"></i>
                <span class="fw-bold">${item.quantity}</span>
                <i class="fas fa-plus-circle ms-2 cursor-pointer" onclick="changeQty(${index}, 1)"></i>
            </div>
            <div class="col-2 text-center fw-bold">₱${itemTotal.toFixed(2)}</div>
            <div class="col-2 text-center"><i class="fas fa-trash-alt cursor-pointer" onclick="removeItem(${index})"></i></div>`;
        cartItemsWrapper.appendChild(row);
    });

    // Attach listeners after render — avoids double-toggle bug
    cartItemsWrapper.querySelectorAll('.cart-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', (e) => {
            const index = parseInt(e.target.dataset.index);
            cartData[index].checked = e.target.checked;
            recalcSubtotal();
        });
    });

    if (subtotalEl) subtotalEl.innerText = `₱${subtotal.toFixed(2)}`;
}

function recalcSubtotal() {
    let subtotal = 0;
    cartData.forEach(item => {
        if (item.checked) subtotal += (item.price * item.quantity);
    });
    if (subtotalEl) subtotalEl.innerText = `₱${subtotal.toFixed(2)}`;
}

/* --- CHECKOUT PAGE --- */
async function loadCheckout() {
    if (!checkoutWrapper) return;
    console.log("Checkout detected, loading...");

    const params = new URLSearchParams(window.location.search);
    const savedIds = params.get('ids') ? params.get('ids').split(',') : [];
    console.log("IDs from URL:", savedIds);

    if (savedIds.length === 0) {
        checkoutWrapper.innerHTML = '<div class="py-5 text-center text-muted">No items selected.</div>';
        const totalEl = document.getElementById('checkout-total-val');
        if (totalEl) totalEl.innerText = '₱0.00';
        return;
    }

    const allItems = await fetchCartData();
    const filteredItems = allItems.filter(item => savedIds.includes(String(item.cart_item_id)));
    console.log("Filtered items for checkout:", filteredItems);

    renderCheckoutUI(filteredItems);
}

function renderCheckoutUI(items) {
    let total = 0;
    checkoutWrapper.innerHTML = '';

    if (items.length === 0) {
        checkoutWrapper.innerHTML = '<div class="py-5 text-center text-muted">No items selected.</div>';
        const totalEl = document.getElementById('checkout-total-val');
        if (totalEl) totalEl.innerText = '₱0.00';
        return;
    }

    items.forEach(item => {
        const sub = parseFloat(item.price) * item.quantity;
        total += sub;
        checkoutWrapper.innerHTML += `
            <div class="row cart-row align-items-center mx-0 py-4 border-bottom">
                <div class="col-6 d-flex align-items-center ps-5">
                    <div class="img-container-base"><img src="${item.image_url}" class="product-img-checkout"></div>
                    <span class="ms-4 fw-bold">${item.name}</span>
                </div>
                <div class="col-2 text-center">₱${parseFloat(item.price).toFixed(2)}</div>
                <div class="col-2 text-center">${item.quantity}</div>
                <div class="col-2 text-center fw-bold">₱${sub.toFixed(2)}</div>
            </div>`;
    });

    const totalEl = document.getElementById('checkout-total-val');
    if (totalEl) totalEl.innerText = `₱${total.toFixed(2)}`;
}

/* --- ADDRESS LOGIC --- */
window.resetToDefault = () => {
    const nameEl = document.getElementById('display-name');
    const addrEl = document.getElementById('display-address');
    if (nameEl) nameEl.innerText = defaultAddress.name;
    if (addrEl) addrEl.innerText = defaultAddress.address;
};

window.openAddressModal = () => {
    const currentAddress = document.getElementById('display-address').innerText;
    const modalAddrInput = document.getElementById('edit-address');
    if (modalAddrInput) modalAddrInput.value = currentAddress;

    const modal = document.getElementById('addressModal');
    if (modal) {
        modal.style.setProperty('display', 'flex', 'important');
    }
};

window.closeAddressModal = () => {
    const modal = document.getElementById('addressModal');
    if (modal) {
        modal.style.setProperty('display', 'none', 'important');
    }
};

window.saveTemporaryAddress = () => {
    const newAddress = document.getElementById('edit-address').value;
    const displayAddr = document.getElementById('display-address');
    if (displayAddr && newAddress.trim() !== "") {
        displayAddr.innerText = newAddress;
    }
    closeAddressModal();
};

/* --- CART ACTIONS --- */
window.changeQty = async (index, amount) => {
    const item = cartData[index];
    const newQty = parseInt(item.quantity) + amount;
    if (newQty > 0) {
        item.quantity = newQty;
        renderCart();
        await fetch('../../db/action/toupdatecart.php', {
            method: 'POST',
            body: JSON.stringify({ action: 'update', id: item.cart_item_id, qty: newQty })
        });
    }
};

window.removeItem = async (index) => {
    const id = cartData[index].cart_item_id;
    cartData.splice(index, 1);
    renderCart();
    await fetch('../../db/action/toupdatecart.php', {
        method: 'POST',
        body: JSON.stringify({ action: 'delete', id: id })
    });
};

/* --- INITIALIZATION --- */
document.addEventListener('DOMContentLoaded', () => {
    if (cartItemsWrapper) loadCart();
    if (checkoutWrapper) loadCheckout();

    // Sync address from DB data
    if (document.getElementById('display-name')) {
        window.resetToDefault();
    }

    // ── Cart page: Checkout button ──
    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const selectedIds = cartData
                .filter(i => i.checked)
                .map(i => String(i.cart_item_id));

            if (selectedIds.length === 0) {
                const sleekModal = document.getElementById('selectionEmptyModal');
                if (sleekModal) sleekModal.style.display = 'flex';
                return;
            }
            window.location.href = 'checkout.php?ids=' + selectedIds.join(',');
        });
    }

    // ── Checkout page: Place Order button → opens confirm modal ──
    const placeOrderBtn = document.getElementById('place-order-btn');
    if (placeOrderBtn) {
        placeOrderBtn.addEventListener('click', () => {
            const confirmModal = document.getElementById('confirmOrderModal');
            if (confirmModal) {
                confirmModal.style.setProperty('display', 'flex', 'important');
            }
        });
    }

    // ── Confirm modal: Cancel button ──
    const cancelOrderBtn = document.getElementById('cancel-order-btn');
    if (cancelOrderBtn) {
        cancelOrderBtn.addEventListener('click', () => {
            document.getElementById('confirmOrderModal').style.setProperty('display', 'none', 'important');
        });
    }

    // ── Confirm modal: Yes, Place Order button ──
    const confirmBtn = document.getElementById('confirm-order-btn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', async () => {
            const params = new URLSearchParams(window.location.search);
            const selectedIds = params.get('ids') ? params.get('ids').split(',') : [];

            const totalText = document.getElementById('checkout-total-val').innerText;
            const totalAmount = totalText.replace(/[₱,]/g, '');

            const addressId = (window.phpData && window.phpData.address_id)
                ? window.phpData.address_id
                : null;

            try {
                const response = await fetch('../../db/action/toplaceorder.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        total_amount: totalAmount,
                        item_ids: selectedIds,
                        address_id: addressId
                    })
                });

                const data = await response.json();
                console.log("Place order response:", data);

                if (data.status === 'success') {
                    document.getElementById('confirmOrderModal').style.setProperty('display', 'none', 'important');
                    document.getElementById('successOrderModal').style.setProperty('display', 'flex', 'important');
                } else {
                    alert("Error: " + data.message);
                }
            } catch (error) {
                console.error('Error placing order:', error);
                alert("Something went wrong. Please try again.");
            }
        });
    }
});

/* --- MODAL HELPERS --- */
window.closeSleekModal = () => {
    const sleekModal = document.getElementById('selectionEmptyModal');
    if (sleekModal) sleekModal.style.display = 'none';
};