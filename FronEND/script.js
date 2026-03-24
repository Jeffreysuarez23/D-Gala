let quantity = 1;
const itemPrice = 59900;
const shippingCost = 7000;
const freeShippingThreshold = 150000;
let currentGender = 'mujer';

const menuData = {
  mujer: [
    { name: 'Nueva Coleccion', hasSubmenu: true },
    { name: 'Ropa', hasSubmenu: true },
    { name: 'Jeans', hasSubmenu: true },
    { name: 'Camisetas', hasSubmenu: true },
    { name: 'Accesorios', hasSubmenu: true },
    { name: 'Personajes', hasSubmenu: true },
    { name: 'Tarjeta de regalo', hasSubmenu: false }
  ],
  hombre: [
    { name: 'Novedades', hasSubmenu: true },
    { name: 'Ropa', hasSubmenu: true },
    { name: 'Jeans', hasSubmenu: true },
    { name: 'Camisetas', hasSubmenu: true },
    { name: 'Accesorios', hasSubmenu: true },
    { name: 'Zapatos', hasSubmenu: true },
    { name: 'Tarjeta de regalo', hasSubmenu: false }
  ]
};

function openCart() {
  document.getElementById('cartSidebar').classList.add('open');
  document.getElementById('cartOverlay').classList.add('show');
  document.body.style.overflow = 'hidden';
}

function closeCart() {
  document.getElementById('cartSidebar').classList.remove('open');
  document.getElementById('cartOverlay').classList.remove('show');
  document.body.style.overflow = '';
}

function increaseQuantity() {
  quantity++;
  updateQuantityDisplay();
}

function decreaseQuantity() {
  if (quantity > 1) {
    quantity--;
    updateQuantityDisplay();
  }
}

function updateQuantityDisplay() {
  document.getElementById('quantity').textContent = quantity;
  document.getElementById('cart-count').textContent = quantity;
  
  const subtotal = itemPrice * quantity;
  let total = subtotal + shippingCost;
  let shipping = shippingCost;
  
  if (subtotal >= freeShippingThreshold) {
    total = subtotal;
    shipping = 0;
    document.getElementById('shipping-cost').textContent = 'GRATIS';
    document.querySelector('.free-shipping').innerHTML = '<strong>¡Felicidades! Tienes ENVÍO GRATUITO</strong>';
  } else {
    document.getElementById('shipping-cost').textContent = `$ ${shipping.toLocaleString()}`;
    const remaining = freeShippingThreshold - subtotal;
    document.querySelector('.free-shipping').innerHTML = `Faltan $ ${remaining.toLocaleString()} para tu <strong>ENVÍO GRATUITO</strong>`;
  }
  
  document.getElementById('item-count').textContent = `${quantity} artículo${quantity > 1 ? 's' : ''}`;
  document.getElementById('item-total').textContent = `$ ${subtotal.toLocaleString()}`;
  document.getElementById('total-amount').textContent = `$ ${total.toLocaleString()}`;
  
  document.querySelector('.cart-header h5').textContent = `Mi carrito (${quantity})`;
}

function removeItem() {
  if (confirm('¿Estás seguro de que quieres eliminar este producto?')) {
    closeCart();
    document.getElementById('cart-count').textContent = '0';
  }
}

function toggleDiscount() {
  const arrow = document.getElementById('discount-arrow');
  arrow.classList.toggle('fa-chevron-right');
  arrow.classList.toggle('fa-chevron-down');
}

function openNavSidebar(gender) {
  currentGender = gender;
  const sidebar = document.getElementById('sidebar');
  const overlay = document.querySelector('.sidebar-overlay');
  
  sidebar.classList.add('show');
  overlay.classList.add('show');
  document.body.style.overflow = 'hidden';
  
  switchGender(gender);
}

function closeNavSidebar() {
  const sidebar = document.getElementById('sidebar');
  const overlay = document.querySelector('.sidebar-overlay');
  
  sidebar.classList.remove('show');
  overlay.classList.remove('show');
  document.body.style.overflow = '';
}

function switchGender(gender) {
  currentGender = gender;

  document.querySelectorAll('.gender-tab').forEach(tab => {
    tab.classList.remove('active');
  });
  document.getElementById(gender + '-tab').classList.add('active');

  updateMenu(gender);
}

function updateMenu(gender) {
  const menu = document.getElementById('sidebar-menu');
  const items = menuData[gender];
  
  menu.innerHTML = '';
  
  items.forEach(item => {
    const li = document.createElement('li');
    li.innerHTML = `
      <a href="#" onclick="handleMenuClick('${item.name}', '${gender}')">
        ${item.name}
        ${item.hasSubmenu ? '<span class="arrow">›</span>' : ''}
      </a>
    `;
    menu.appendChild(li);
  });
}

function handleMenuClick(itemName, gender) {
  console.log(`Clicked on ${itemName} for ${gender}`);
  closeNavSidebar();
}

document.addEventListener('keydown', function(event) {
  if (event.key === 'Escape') {
    closeCart();
    closeNavSidebar();
  }
});

document.getElementById('sidebar').addEventListener('click', function(e) {
  e.stopPropagation();
});

document.getElementById('cartSidebar').addEventListener('click', function(e) {
  e.stopPropagation();
});

document.addEventListener('DOMContentLoaded', function() {
  updateQuantityDisplay();
});