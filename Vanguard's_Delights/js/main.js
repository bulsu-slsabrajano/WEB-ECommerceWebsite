// SAMPLE HARDCODED DATA
// You can easily add or remove objects here later.
let cartData = [
    {
        id: 101,
        name: "Chocolate Loaf",
        price: 50.00,
        qty: 1,
        img: "../assets/product1.png" // Update with your actual image path
    },
    {
        id: 102,
        name: "Red Velvet Sandwich",
        price: 80.00,
        qty: 1,
        img: "../assets/product2.png"
    },
    {
        id: 103,
        name: "Caramel Cookie",
        price: 50.00,
        qty: 1,
        img: "../assets/product3.png"
    },
    {
        id: 104,
        name: "Matcha Lava",
        price: 120.00,
        qty: 1,
        img: "../assets/product4.png"
    }
];

const wrapper = document.getElementById('cart-items-wrapper');
const subtotalEl = document.getElementById('cart-subtotal');

// FUNCTION TO DRAW THE CART
function renderCart() {
    wrapper.innerHTML = ''; // Clear current view
    let subtotal = 0;

    if (cartData.length === 0) {
        wrapper.innerHTML = '<div class="text-center py-5 text-muted">Your cart is empty</div>';
        subtotalEl.innerText = '₱0.00';
        return;
    }

  cartData.forEach((item, index) => {
    const itemTotal = item.price * item.qty;
    subtotal += itemTotal;

    const row = document.createElement('div');
    row.className = 'row cart-row align-items-center mx-0 px-0';
    row.innerHTML = `
        <div class="col-4 d-flex align-items-center ps-5">
            <input type="checkbox" class="form-check-input">
            <img src="${item.img}" class="product-img">
            <span class="ms-3 fw-bold">${item.name}</span>
        </div>
        <div class="col-2 text-center text-secondary">₱${item.price.toFixed(2)}</div>
        <div class="col-2 text-center">
            <div class="qty-controls d-flex justify-content-center align-items-center">
                <i class="fas fa-minus-circle" style="cursor:pointer" onclick="changeQty(${index}, -1)"></i>
                <span class="qty-val mx-3 fw-bold">${item.qty}</span>
                <i class="fas fa-plus-circle" style="cursor:pointer" onclick="changeQty(${index}, 1)"></i>
            </div>
        </div>
        <div class="col-2 text-center fw-bold">₱${itemTotal.toFixed(2)}</div>
        <div class="col-2 text-center">
            <i class="fas fa-trash-alt" style="cursor:pointer" onclick="removeItem(${index})"></i>
        </div>
    `;
    wrapper.appendChild(row);
});

    // Update Subtotal Display
    subtotalEl.innerText = `₱${subtotal.toFixed(2)}`;
}

// LOGIC: CHANGE QUANTITY
window.changeQty = (index, amount) => {
    if (cartData[index].qty + amount > 0) {
        cartData[index].qty += amount;
        renderCart(); // Refresh the UI
    }
};

// LOGIC: REMOVE ITEM
window.removeItem = (index) => {
    // Optional: Add a confirmation
    if(confirm("Remove this item from cart?")) {
        cartData.splice(index, 1);
        renderCart(); // Refresh the UI
    }
    document.addEventListener('DOMContentLoaded', () => {
    console.log("WST Error Page Loaded.");

    const title404 = document.querySelector('.not-found-text2');
    
    // Simpleng animation para sa text pag-load
    if (title404) {
        title404.style.opacity = '0';
        title404.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            title404.style.transition = 'all 0.8s ease-out';
            title404.style.opacity = '1';
            title404.style.transform = 'translateY(0)';
        }, 200);
    }
});
};

// INITIAL RUN
renderCart();