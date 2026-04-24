let menuData = [];
let cart = [];

// Format Rupiah
function formatRupiah(num) {
  return "Rp " + num.toLocaleString('id-ID');
}

// Load Menu dari API
async function loadMenu() {
  try {
    const response = await fetch("api/info.php?table=dining_menu");
    const data = await response.json();
    if (Array.isArray(data)) {
      menuData = data;
      renderMenu();
    } else {
      document.getElementById('menu-container').innerHTML =
        "<p class='text-center text-red-300'>Gagal memuat menu.</p>";
    }
  } catch (err) {
    document.getElementById('menu-container').innerHTML =
      "<p class='text-center text-red-300'>Koneksi gagal.</p>";
  }
}

// Render Menu
function renderMenu() {
  const container = document.getElementById('menu-container');
  container.innerHTML = '';
  menuData.forEach((item, index) => {
    const card = document.createElement('div');
    card.className = 'card text-center';
    card.innerHTML = `
      <img src="${item.image_url}" alt="${item.name}" class="w-full h-40 object-cover rounded-lg mb-2">
      <h3 class="font-semibold text-lg">${item.name}</h3>
      <p class="text-yellow-300 mb-1">${formatRupiah(item.price)}</p>
      <button onclick="addToCart(${index})"
        class="bg-yellow-400 hover:bg-yellow-500 text-gray-900 px-4 py-1 rounded-lg font-semibold mt-2">
        Tambah
      </button>
    `;
    container.appendChild(card);
  });
}

// Tambah ke Cart
function addToCart(index) {
  const item = menuData[index];
  const existing = cart.find(i => i.id === item.id);
  if (existing) {
    existing.qty += 1;
  } else {
    cart.push({ ...item, qty: 1 });
  }
  updateCart();
}

// Kurangi Qty
function decreaseQty(index) {
  cart[index].qty--;
  if (cart[index].qty <= 0) {
    cart.splice(index, 1);
  }
  updateCart();
}

// Tambah Qty
function increaseQty(index) {
  cart[index].qty++;
  updateCart();
}

// Hapus Item
function removeFromCart(index) {
  cart.splice(index, 1);
  updateCart();
}

// Update Cart View
function updateCart() {
  const cartList = document.getElementById('cart-list');
  const totalElem = document.getElementById('cart-total');
  cartList.innerHTML = '';
  let total = 0;

  cart.forEach((item, i) => {
    total += item.price * item.qty;
    const row = document.createElement('div');
    row.className = 'cart-item';
    row.innerHTML = `
      <div class="flex-1">${item.name}</div>
      <div class="flex items-center space-x-2">
        <button class="qty-btn" onclick="decreaseQty(${i})">−</button>
        <span>${item.qty}</span>
        <button class="qty-btn" onclick="increaseQty(${i})">+</button>
      </div>
      <div class="w-24 text-right">${formatRupiah(item.price * item.qty)}</div>
      <button onclick="removeFromCart(${i})" class="text-red-400 hover:text-red-500 ml-2">Hapus</button>
    `;
    cartList.appendChild(row);
  });

  totalElem.textContent = "Total: " + formatRupiah(total);
}

// Kirim Pesanan
document.getElementById("submitOrder").addEventListener("click", async () => {
  if (cart.length === 0) {
    alert("Keranjang masih kosong!");
    return;
  }
  try {
    const res = await fetch("api/posorder.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ cart })
    });
    const result = await res.json();
    if (result.status === "success") {
      alert("Pesanan berhasil dikirim ke resepsionis!");
      cart = [];
      updateCart();
    } else {
      alert("Gagal mengirim pesanan!");
    }
  } catch (e) {
    alert("Koneksi gagal, coba lagi!");
  }
});

window.onload = loadMenu;