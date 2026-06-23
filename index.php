<?php
// v14.0 - FINAL CLEAN (Bilingual Read-Only)
include 'config.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <!-- Allow autoplay for audio/video in WebView -->
  <meta name="mobile-web-app-capable" content="yes">
  <meta http-equiv="Feature-Policy" content="autoplay 'self'">
  <title>Hotel TV Launcher</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      font-family: 'Inter', sans-serif;
      overflow: hidden;
    }

    .bg-launcher {
      background-size: cover;
      background-position: center center;
      background-repeat: no-repeat;
      transition: background-image 0.4s ease-in-out;
    }

    .bg-overlay {
      background-color: rgba(0, 0, 0, 0.4);
    }

    /* Kontainer Menu */
    .menu-container-wrapper {
      width: 100%;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .menu-scroll-container {
      display: flex;
      align-items: center;
      overflow: hidden;
      width: 1150px;
      height: 180px;
      justify-content: flex-start;
    }

    #main-menu-items {
      display: flex;
      flex-wrap: nowrap;
      white-space: nowrap;
      transition: transform 0.3s ease-in-out;
      align-items: center;
    }

    .nav-arrow {
      color: rgba(255, 255, 255, 0.5);
      transition: all 0.2s ease;
    }

    .nav-arrow.active {
      color: rgba(255, 255, 255, 1);
      transform: scale(1.2);
    }

    /* Ikon Menu Efek 3D */
    .menu-item {
      transition: all 0.25s ease-in-out;
      width: 135px;
      height: 135px;
      margin: 0 4px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      border-radius: 22px;
      text-align: center;
      flex-shrink: 0;
      background: rgba(255, 255, 255, 0.05);
      filter: brightness(0.85);
      border: none;
      box-shadow: 0 0 0 transparent;
      position: relative;
    }

    .menu-item::after {
      content: "";
      position: absolute;
      bottom: 12px;
      left: 50%;
      transform: translateX(-50%);
      width: 60%;
      height: 8px;
      border-radius: 50%;
      background: radial-gradient(ellipse at center, rgba(255, 255, 255, 0.25), transparent 70%);
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .menu-item.focused {
      transform: translateY(-6px) scale(1.1);
      filter: brightness(1.25);
      background: radial-gradient(circle at top, rgba(255, 255, 255, 0.15), rgba(0, 0, 0, 0.4));
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.55), 0 0 25px rgba(255, 255, 255, 0.25) inset;
    }

    .menu-item.focused::after {
      opacity: 1;
    }

    .menu-item-icon {
      width: 56px;
      height: 56px;
      object-fit: contain;
      margin-bottom: 6px;
      transform: scale(0.95);
      transition: transform 0.25s ease;
    }

    .menu-item.focused .menu-item-icon {
      transform: scale(1.12);
    }

    .menu-item span {
      font-size: 0.9rem;
      transition: color 0.3s ease;
    }

    .menu-item.focused span {
      color: #fff;
    }

    .hidden {
      display: none;
    }

    #boot-loading {
      position: fixed;
      inset: 0;
      z-index: 99999;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(0, 0, 0, 0.85);
    }

    .loader {
      border: 4px solid #f3f3f3;
      border-top: 4px solid #3498db;
      border-radius: 50%;
      width: 40px;
      height: 40px;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }

    /* RunText */
    .marquee-container {
      width: 100%;
      background-color: rgba(0, 0, 0, 0.6);
      overflow: hidden;
      position: absolute;
      bottom: 0;
      left: 0;
      white-space: nowrap;
      box-sizing: border-box;
      padding: 6px 0;
    }

    .marquee-text {
      display: inline-block;
      color: white;
      font-size: 1rem;
      line-height: 1.5rem;
      animation: marquee 30s linear infinite;
      padding-left: 100%;
    }

    @keyframes marquee {
      0% {
        transform: translateX(0%);
      }

      100% {
        transform: translateX(-100%);
      }
    }

    /* === Notification Card === */
    .notif-card {
      background: rgba(0, 0, 0, 0.20);
      backdrop-filter: blur(8px);
      padding: 40px;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.55);
      border: 1px solid rgba(255, 255, 255, 0.12);
    }

    /* Tombol OK (override karena kamu ada rule *:focus { box-shadow:none!important } ) */
    .notif-btn {
      background: #facc15;
      color: #111;
      padding: 8px 34px;
      border-radius: 9999px;
      font-weight: 500;
      border: 2px solid transparent;
      transition: transform .15s ease, box-shadow .15s ease, background .15s ease, border-color .15s ease;
    }


    #notification-ok:focus {
      box-shadow: 0 0 15px rgba(250, 204, 21, 0.6) !important;
      transform: scale(1.05);
      background: #fff;
      border-color: #facc15;
    }

    #notification-text img {
      max-width: 100%;
      max-height: 400px;
      /* Membatasi tinggi gambar */
      margin-bottom: 20px;
      border-radius: 8px;
      /* Optional: membuat gambar lebih elegan dengan rounded corners */
    }



    .header-time {
      font-size: 2.5rem;
      line-height: 1.1;
      font-weight: 700;
    }

    .header-date {
      font-size: 1rem;
      color: #d1d5db;
    }

    .weather-icon {
      width: 2.5rem;
      height: 2.5rem;
      margin-right: 0.5rem;
      filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.4));
    }

    .guest-hello {
      font-size: 0.75rem;
      color: #d1d5db;
    }

    .guest-name {
      font-size: 1.125rem;
      font-weight: 600;
    }

    .room-label {
      font-size: 0.75rem;
      color: #d1d5db;
    }

    .room-number {
      font-size: 1.125rem;
      font-weight: 600;
    }

    .guest-avatar {
      width: 48px;
      height: 48px;
      border-radius: 9999px;
      background-color: #374151;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }

    .guest-avatar svg {
      width: 24px;
      height: 24px;
      color: #9ca3af;
    }

    .guest-info-wrapper {
      display: flex;
      align-items: center;
      gap: 1rem;
      text-align: right;
    }

    .guest-room-wrapper {
      padding-left: 1rem;
      border-left: 1px solid #4b5563;
    }

    /* Register Screen */
    .register-title {
      font-size: 2rem;
      line-height: 2.5rem;
      font-weight: 700;
      color: #facc15;
    }

    .register-desc {
      font-size: 1.125rem;
      line-height: 1.75rem;
      color: #d1d5db;
    }

    .register-codebox {
      background: white;
      color: black;
      font-size: 2rem;
      line-height: 2.5rem;
      font-family: monospace;
      font-weight: 700;
      padding: 1.5rem;
      border-radius: .5rem;
      display: inline-block;
      min-width: 12ch;
      text-align: center;
    }

    .register-status {
      color: #facc15;
      font-size: 1rem;
      line-height: 1.5rem;
      margin-top: 1.5rem;
    }

    .register-footnote {
      color: #9ca3af;
      font-size: .75rem;
      line-height: 1rem;
      margin-top: 1.5rem;
    }

    /* Hapus highlight kuning default */
    *:focus {
      outline: none !important;
      border: none !important;
      box-shadow: none !important;
    }

    .menu-item:focus-visible {
      outline: none !important;
      border: none !important;
      box-shadow: none !important;
    }
  </style>
</head>

<body class="bg-gray-900 text-white h-screen w-screen overflow-hidden">

  <div id="boot-loading">
  </div>

  <div id="launcher-container" class="relative h-full w-full bg-launcher hidden">
    <div class="absolute inset-0 bg-overlay"></div>
    <div class="relative z-10 h-full w-full flex flex-col p-8 md:p-12">
      <header class="flex justify-between items-start">
        <div class="flex items-center space-x-4">
          <img id="weather-icon-display" src="" alt="Cuaca" class="weather-icon hidden">
          <div>
            <p class="header-time" id="time-display">--:--</p>
            <p class="header-date" id="date-display">Loading...</p>
          </div>
        </div>

        <div class="guest-info-wrapper text-right">
          <div class="text-right">
            <p class="guest-hello">Selamat Datang</p>
            <p class="guest-name" id="guest-name-display">Fetching...</p>
          </div>
          <div class="guest-avatar"> <svg fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd">
              </path>
            </svg>
          </div>
          <div class="guest-room-wrapper text-right">
            <p class="room-label">Room</p>
            <p class="room-number" id="room-number-display">...</p>
          </div>
        </div>

      </header>

      <div class="flex-grow"></div>

      <footer class="menu-container-wrapper">
        <div id="nav-arrow-left" class="nav-arrow p-2">
          <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd"
              d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
              clip-rule="evenodd"></path>
          </svg>
        </div>
        <div class="menu-scroll-container">
          <div id="main-menu-items"></div>
        </div>
        <div id="nav-arrow-right" class="nav-arrow active p-2">
          <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd"
              d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
              clip-rule="evenodd"></path>
          </svg>
        </div>
      </footer>
    </div>

    <div class="marquee-container">
      <div class="marquee-text" id="marquee-text-content">Loading running text...</div>
    </div>
    <div id="disabled-screen"
      class="absolute inset-0 bg-gray-900 z-50 hidden justify-center items-center p-10 text-center">
      <div>
        <svg class="w-24 h-24 mx-auto text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
        </svg>
        <h1 class="text-3xl text-white font-bold mt-8">Launcher Disabled by Admin</h1>
        <p class="text-lg text-gray-400 mt-4">Please contact hotel staff to enable this TV.</p>
      </div>
    </div>
  </div>

  <!-- Notification Popup (Card) -->
  <div id="notification-popup" class="hidden fixed inset-0 z-[9999] flex items-center justify-center">
    <!-- backdrop -->
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>

    <!-- card -->
    <div class="relative notif-card w-11/12 max-w-4xl">
      <h2 id="notification-title" class="text-3xl font-bold text-yellow-400 mb-3">Notification</h2>

      <div id="notification-text" class="text-white text-lg leading-relaxed whitespace-pre-line">
        Pesan notifikasi di sini...
      </div>

      <div class="mt-3 flex justify-end">
        <button id="notification-ok" type="button" class="notif-btn" tabindex="0">
          OK
        </button>
      </div>
    </div>
  </div>


  <div id="reg-badge"
    style="top: 15px; left: 15px;" class="hidden absolute z-40 bg-black bg-opacity-70 text-yellow-400 px-3 py-1.5 rounded-lg text-sm font-mono border border-yellow-500 flex items-center gap-1.5">
    <span id="reg-badge-label" class="text-gray-300 text-xs">Reg:</span>
    <span id="reg-badge-code">------</span>
    <span id="reg-badge-status" class="text-green-400 text-xs hidden">✓</span>
    <span id="reg-badge-ip" class="text-gray-400 text-xs">IP: --</span>
    <img id="reg-badge-qr" width="76" height="76" style="vertical-align:middle" alt="QR">
  </div>
  <script>
    const PAGE_ROUTES = {
      information: 'information.html?v=17',
      general_info: 'general_info.html?v=13',
      general_information: 'general_info.html?v=13',
      facilities: 'facilities.html?v=6',
      amenities: 'amenities.html?v=14',
      dining: 'dining.html?v=9',
      promotion: 'promotion.html?v=7',
      transport: 'transport.html',
      iptv_web: 'iptv.html',
      info_playlist: 'info_player.html'
    };

    const WEB_URLS = {
      netflix: 'https://www.netflix.com',
      spotify: 'https://open.spotify.com',
      vidio: 'https://www.vidio.com',
      youtube: 'https://www.youtube.com'
    };
    // == Kunci localStorage 
    const STORAGE_REG_CODE_KEY = 'myRegistrationCode_v8_final';
    const STORAGE_DEVICE_ID_KEY = 'myDeviceID_v8_final';
    const STORAGE_GUEST_NAME_KEY = 'guest_name_v8_final';
    const STORAGE_ROOM_NUM_KEY = 'room_number_v8_final';
    const STORAGE_LANG_KEY = 'app_lang'; // KUNCI BAHASA

    const POLLING_INTERVAL = 5000;
    const GUEST_POLLING_INTERVAL = 10000;

    let pollingIntervalId = null;
    let currentDeviceID = null;
    let menuItems = [];
    let currentFocusIndex = 0;
    let currentGuestName = "Fetching...";
    const DEFAULT_FOCUS_KEY = 'facilities';
    // === Touch swipe ===
    let touchStartX = 0;
    let touchStartY = 0;
    const SWIPE_THRESHOLD = 50;


    // === Elemen utama ===
    const bootLoading = document.getElementById('boot-loading');
    const launcherContainer = document.getElementById('launcher-container');
    const disabledScreen = document.getElementById('disabled-screen');
    const regBadge = document.getElementById('reg-badge');
    const regBadgeCode = document.getElementById('reg-badge-code');
    const regBadgeStatus = document.getElementById('reg-badge-status');
    const marqueeText = document.getElementById('marquee-text-content');
    const menuItemsContainer = document.getElementById('main-menu-items');
    const menuScrollContainer = document.querySelector('.menu-scroll-container');
    const guestNameEl = document.getElementById('guest-name-display');
    const roomNumberEl = document.getElementById('room-number-display');
    const timeEl = document.getElementById('time-display');
    const dateEl = document.getElementById('date-display');
    const weatherIconEl = document.getElementById('weather-icon-display');
    const navArrowLeft = document.getElementById('nav-arrow-left');
    const navArrowRight = document.getElementById('nav-arrow-right');

    const notificationPopup = document.getElementById('notification-popup');
    const notificationTitle = document.getElementById('notification-title');
    const notificationText = document.getElementById('notification-text');
    const notificationOkBtn = document.getElementById('notification-ok');

    let notificationTimeoutId = null;
    let lastFocusedElementBeforeNotif = null;
    let lastNotificationCache = null; // opsional: anti spam notif yang sama


    // === Fungsi dasar ===
    function clearLocalStorage() {
      try {
        localStorage.removeItem(STORAGE_REG_CODE_KEY);
        localStorage.removeItem(STORAGE_DEVICE_ID_KEY);
        localStorage.removeItem(STORAGE_GUEST_NAME_KEY);
        localStorage.removeItem(STORAGE_ROOM_NUM_KEY);
        localStorage.removeItem(STORAGE_LANG_KEY);
      } catch (e) { }
    }

    function updateQR(code) {
      const qr = document.getElementById('reg-badge-qr');
      if (qr && code) qr.src = 'https://api.qrserver.com/v1/create-qr-code/?size=64x64&data=' + encodeURIComponent(code);
    }

    function getStableRegistrationCode() {
      const params = new URLSearchParams(window.location.search);
      if (params.has('reset')) {
        clearLocalStorage();
        window.location.replace(window.location.pathname);
        return null;
      }

      let code = localStorage.getItem(STORAGE_REG_CODE_KEY);
      if (code) {
        regBadgeCode.textContent = code;
        updateQR(code);
        return code;
      }

      const saved = localStorage.getItem(STORAGE_DEVICE_ID_KEY);
      if (saved) {
        regBadgeCode.textContent = saved;
        updateQR(saved);
        return saved;
      }

      code = 'TV-' + Math.random().toString(36).substr(2, 6).toUpperCase();
      localStorage.setItem(STORAGE_REG_CODE_KEY, code);
      regBadgeCode.textContent = code;
      updateQR(code);
      return code;
    }

    function detectLocalIP() {
      const ipEl = document.getElementById('reg-badge-ip');
      try {
        const pc = new RTCPeerConnection({
          iceServers: [],
          iceTransportPolicy: 'all',
          rtcpMuxPolicy: 'require'
        });
        pc.createDataChannel('');
        pc.createOffer().then(offer => pc.setLocalDescription(offer));
        let found = false;

        pc.onicecandidate = (e) => {
          if (!e.candidate || found) return;
          const str = e.candidate.candidate;

          // Priority 1: typ host — IP LAN langsung
          if (/typ host/.test(str)) {
            const m = str.match(/(\d+\.\d+\.\d+\.\d+)/);
            if (m && m[1] !== '127.0.0.1') {
              ipEl.textContent = 'IP: ' + m[1];
              found = true;
              pc.close();
              updateDeviceIP(m[1]);
              return;
            }
          }

          // Priority 2: typ srflx -> ambil raddr (private IP di balik NAT)
          if (/typ srflx/.test(str)) {
            const m = str.match(/raddr\s(\d+\.\d+\.\d+\.\d+)/);
            if (m && m[1] !== '127.0.0.1') {
              ipEl.textContent = 'IP: ' + m[1];
              found = true;
              pc.close();
              updateDeviceIP(m[1]);
              return;
            }
          }
        };

        // Timeout: beresin koneksi kalo gak dapet apa2
        setTimeout(() => {
          if (!found) pc.close();
        }, 5000);
      } catch (e) {
        ipEl.textContent = 'IP: N/A';
      }
    }

    function updateDeviceIP(ip) {
      const deviceId = localStorage.getItem(STORAGE_DEVICE_ID_KEY);
      if (!deviceId || !ip) return;
      fetch('./api.php?action=registerDeviceIp', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ device_id: deviceId, device_ip: ip })
      }).catch(() => {});
    }

    async function checkRegistrationStatus(code) {
      if (!code) return;
      try {
        const res = await fetch(`./api.php?action=checkRegistration&device_id=${code}&_=${Date.now()}`);
        const data = await res.json();
        if (data.status === 'success' && data.is_registered) {
          stopPolling();
          currentDeviceID = code;
          localStorage.removeItem(STORAGE_REG_CODE_KEY);
          localStorage.setItem(STORAGE_DEVICE_ID_KEY, currentDeviceID);
          regBadgeCode.textContent = currentDeviceID;
          regBadge.classList.add('hidden');
          updateQR(currentDeviceID);

          // Sinkronisasi device_id ke Android SharedPreferences
          if (window.AndroidBridge && typeof window.AndroidBridge.setDeviceId === 'function') {
            window.AndroidBridge.setDeviceId(currentDeviceID);
          }
        }
      } catch (err) {
        // silently ignore polling errors
      }
    }

    function startPolling(code) {
      if (!code) return;
      checkRegistrationStatus(code);
      pollingIntervalId = setInterval(() => checkRegistrationStatus(code), POLLING_INTERVAL);
    }
    function stopPolling() { clearInterval(pollingIntervalId); }

    async function pollGuestInfo() {
      if (!currentDeviceID) return;

      try {
        const res = await fetch(`./api.php?action=getGuestInfo&device_id=${currentDeviceID}&_=${Date.now()}`);
        const guestData = await res.json();

        if (guestData.status === 'success') {
          const newGuestName = guestData.data.guest_name;
          if (newGuestName !== currentGuestName) {
            currentGuestName = newGuestName;
            guestNameEl.textContent = newGuestName;
            localStorage.setItem(STORAGE_GUEST_NAME_KEY, newGuestName);
          }
        }
      } catch (err) {
        console.warn("Polling nama tamu gagal.", err);
      }
    }

    // === FUNGSI BAHASA (READ ONLY) ===
    function getCurrentLang() {
      return localStorage.getItem(STORAGE_LANG_KEY) || 'id';
    }

    function applyLanguageText(lang) {
      // Update teks UI statis
      if (lang === 'zh') {
        document.querySelector('.guest-hello').textContent = '欢迎';
        document.querySelector('.room-label').textContent = '房间';
      } else if (lang === 'en') {
        document.querySelector('.guest-hello').textContent = 'Welcome';
        document.querySelector('.room-label').textContent = 'Room';
      } else {
        document.querySelector('.guest-hello').textContent = 'Selamat Datang';
        document.querySelector('.room-label').textContent = 'Kamar';
      }
    }

    function getWeatherIcon(iconCode) {
      return `https://openweathermap.org/img/wn/${iconCode}@2x.png`;
    }

    // === Pemuatan Data Launcher ===
    async function loadLauncherData(deviceID, lang) {
      try {
        const statusRes = await fetch(`./api.php?action=getStatus&_=${Date.now()}`);
        const statusData = await statusRes.json();
        if (statusData.status === 'success' && !statusData.is_launcher_enabled) {
          launcherContainer.classList.add('hidden');
          disabledScreen.classList.remove('hidden');
          bootLoading.style.display = 'none';
          if (window.AndroidBridge && typeof window.AndroidBridge.hideLoadingScreen === 'function') {
            window.AndroidBridge.hideLoadingScreen();
          }
          bootLoading.style.display = 'none';
          return;
        }

        // Ambil data dengan parameter bahasa yang dipilih di halaman Greeting
        const [guestRes, marqueeRes, appsRes, weatherRes] = await Promise.all([
          fetch(`./api.php?action=getGuestInfo&device_id=${deviceID}&_=${Date.now()}`),
          fetch(`./api.php?action=getMarqueeText&lang=${lang}&_=${Date.now()}`),
          fetch(`./api.php?action=getAppVisibility&lang=${lang}&_=${Date.now()}`),
          fetch(`./api.php?action=getWeather&lang=${lang}&_=${Date.now()}`)
        ]);

        // Info tamu
        const guestData = guestRes.ok ? await guestRes.json() : {};
        if (guestData.status === 'success') {
          const guestName = guestData.data.guest_name;
          const roomNumber = guestData.data.room_number;
          guestNameEl.textContent = guestName;
          roomNumberEl.textContent = roomNumber;
          currentGuestName = guestName;
          localStorage.setItem(STORAGE_GUEST_NAME_KEY, guestName);
          localStorage.setItem(STORAGE_ROOM_NUM_KEY, roomNumber);
        }

        // Terapkan teks bahasa
        applyLanguageText(lang);

        // Runtext
        const marqueeData = marqueeRes.ok ? await marqueeRes.json() : {};
        if (marqueeData.status === 'success') marqueeText.textContent = marqueeData.text;

        // Menu ikon
        const appsData = appsRes.ok ? await appsRes.json() : {};
        if (appsData.status === 'success' && appsData.apps) {
          buildMainMenu(appsData.apps);
          const idx = appsData.apps.findIndex(a => a.app_key === DEFAULT_FOCUS_KEY);
          if (idx !== -1) currentFocusIndex = idx;
        }

        // Data Cuaca
        const weatherData = weatherRes.ok ? await weatherRes.json() : {};
        let weatherInfo = null;
        if (weatherData.status === 'success') {
          weatherInfo = weatherData.data;
          weatherIconEl.src = getWeatherIcon(weatherData.data.icon);
          weatherIconEl.classList.remove('hidden');
        }

        // Tampilkan launcher
        launcherContainer.classList.remove('hidden');
        disabledScreen.classList.add('hidden');
        bootLoading.style.display = 'none';

        setFocus(currentFocusIndex);
        setTimeout(() => {
          if (menuItems[currentFocusIndex]) menuItems[currentFocusIndex].classList.add('focused');
          scrollMenu();
        }, 100);

        document.addEventListener('keydown', handleKeyDown);
        document.addEventListener('touchstart', handleTouchStart, { passive: true });
        document.addEventListener('touchend', handleTouchEnd, { passive: true });

        updateClock(weatherInfo);
        setInterval(() => updateClock(weatherInfo), 1000);
        setInterval(pollGuestInfo, GUEST_POLLING_INTERVAL);
        loadDynamicBackground();
        startNotificationPolling();

        if (window.AndroidBridge && typeof window.AndroidBridge.hideLoadingScreen === 'function') {
          window.AndroidBridge.hideLoadingScreen();
        }

      } catch (err) {
        bootLoading.style.display = 'none';
        console.warn('Launcher load error:', err.message);
        if (window.AndroidBridge && typeof window.AndroidBridge.hideLoadingScreen === 'function') {
          window.AndroidBridge.hideLoadingScreen();
        }
      }
    }

    // === Panggil background dinamis dari API ===
    async function loadDynamicBackground() {
      try {
        const res = await fetch(`./api.php?action=getHomeBackground&_=${Date.now()}`);
        const data = await res.json();
        const bgUrl = (data.status === 'success' && data.background_url) ? data.background_url : 'img/hotel3.png';
        const launcherBg = document.querySelector('.bg-launcher');
        if (launcherBg) launcherBg.style.backgroundImage = `url('${bgUrl}')`;
      } catch (err) {
        const launcherBg = document.querySelector('.bg-launcher');
        if (launcherBg) launcherBg.style.backgroundImage = "url('img/hotel3.png')";
      }
    }

    // === Fungsi menu & navigasi ===
    function buildMainMenu(apps) {
      menuItemsContainer.innerHTML = '';
      apps.forEach((app, i) => {
        const el = document.createElement('div');
        el.className = 'menu-item';
        el.tabIndex = 0;
        el.dataset.index = i;
        el.dataset.page = PAGE_ROUTES[app.app_key] || '';
        el.dataset.pkg = app.android_package || '';
        el.dataset.key = app.app_key || '';
        el.dataset.label = app.app_name || 'App';
        el.innerHTML = `<img src="${app.icon_path}" class="menu-item-icon" alt=""><span>${app.app_name}</span>`;

        el.addEventListener('click', () => { handleItemClick(el); });
        el.addEventListener('focus', () => { setFocus(i); scrollMenu(); });
        menuItemsContainer.appendChild(el);
      });
      menuItems = document.querySelectorAll('.menu-item');
      updateArrowVisibility();
    }

    function handleItemClick(item) {
      if (!item) return;
      if (item.dataset.page) {
        if (window.AndroidBridge && typeof window.AndroidBridge.hideLoadingScreen === 'function') {
          window.AndroidBridge.hideLoadingScreen();
        }
        window.location.href = item.dataset.page;
      }
      else if (item.dataset.pkg) {
        if (/android/i.test(navigator.userAgent)) {
          launchNativeApp(item.dataset.pkg, item.dataset.label);
        } else {
          const webUrl = WEB_URLS[item.dataset.key];
          if (webUrl) {
            window.location.href = webUrl;
          } else {
            window.location.href = item.dataset.pkg;
          }
        }
      }
    }


    // function buildMainMenu(apps) {
    //   menuItemsContainer.innerHTML = '';  // Clear existing menu items

    //   apps.forEach((app, i) => {
    //     const el = document.createElement('div');
    //     el.className = 'menu-item';
    //     el.tabIndex = 0; 
    //     el.dataset.index = i;

    //     // Menentukan apakah menu item mengarah ke halaman internal (data-page) atau URL eksternal (data-pkg)
    //     if (app.app_key === 'information') {
    //       el.dataset.page = 'information.html';  // Halaman internal
    //     } else if (app.app_key === 'dining') {
    //       el.dataset.page = 'dining.html'; // Halaman internal
    //     } else if (app.app_key === 'amenities') {
    //       el.dataset.page = 'amenities.html'; // Halaman internal
    //     } else if (app.app_key === 'facilities') {
    //       el.dataset.page = 'facilities.html'; // Halaman internal
    //     } else {
    //       // Jika bukan halaman internal, set data-pkg dengan URL eksternal
    //       el.dataset.pkg = app.android_package || '';  // URL eksternal, misalnya 'https://www.youtube.com/'
    //     }

    //     el.dataset.label = app.app_name || 'App';
    //     el.innerHTML = `<img src="${app.icon_path}" class="menu-item-icon" alt=""><span>${app.app_name}</span>`;

    //     // Event listener untuk handle klik item menu
    //     el.addEventListener('click', () => { handleItemClick(el); });
    //     el.addEventListener('focus', () => { setFocus(i); scrollMenu(); });

    //     menuItemsContainer.appendChild(el);  // Append item ke menu
    //   });

    //   // Setelah menu dibuat, update menu item
    //   menuItems = document.querySelectorAll('.menu-item');
    //   updateArrowVisibility();  // Update visibilitas panah navigasi
    // }

    // function handleItemClick(item) {
    //     if (!item) return;

    //     // Jika ada data-page (menu internal), buka halaman internal
    //     if (item.dataset.page) {
    //         window.location.href = item.dataset.page; // Arahkan ke halaman internal seperti 'information.html', 'facilities.html'
    //     }
    //     // Jika ada data-pkg (URL eksternal), buka URL di browser
    //     else if (item.dataset.pkg) {
    //         console.log("Navigating to:", item.dataset.pkg); // Debugging log untuk URL yang akan dibuka
    //         // Cek apakah aplikasi dijalankan di website (browser) dan bukan Android
    //         if (/android/i.test(navigator.userAgent)) {
    //             // Di perangkat Android, buka aplikasi native (Android)
    //             launchNativeApp(item.dataset.pkg, item.dataset.label);
    //         } else {
    //             // Di website, buka URL di browser (misalnya YouTube, Netflix)
    //             window.location.href = item.dataset.pkg;  // Arahkan ke URL seperti 'https://www.youtube.com/'
    //         }
    //     }
    // }

    function handleKeyDown(e) {
      const key = e.key || '';
      const keyCode = e.keyCode || e.which;

      // Block tombol Back remote TV agar tidak keluar dari WebView
      if (key === 'Back' || keyCode === 4) {
        e.preventDefault();
        return;
      }

      if (!notificationPopup.classList.contains('hidden')) {
        if (key === 'Enter' || key === 'Escape' || key === 'Backspace') {
          e.preventDefault();
          hideNotification();
          return;
        }

        // Block navigasi lain biar fokus tidak lari ke menu
        if (key.startsWith('Arrow')) {
          e.preventDefault();
          notificationOkBtn.focus();
          return;
        }
      }

      const blocked = ['MetaLeft', 'MetaRight', 'Home', 'Escape', 'Back', 'F10'];
      if (blocked.includes(key)) { e.preventDefault(); return; }

      const activeEl = document.activeElement;
      if (activeEl && activeEl.classList.contains('menu-item')) {
        const currentIndex = parseInt(activeEl.dataset.index || '0');
        switch (key) {
          case 'ArrowLeft':
            e.preventDefault();
            if (currentIndex > 0) menuItems[currentIndex - 1].focus();
            break;
          case 'ArrowRight':
            e.preventDefault();
            if (currentIndex < menuItems.length - 1) menuItems[currentIndex + 1].focus();
            break;
          case 'Enter':
            e.preventDefault();
            handleItemClick(activeEl);
            break;
        }
      } else {
        if (key === 'ArrowLeft' || key === 'ArrowRight' || key === 'Enter') {
          if (menuItems.length > 0) menuItems[currentFocusIndex].focus();
        }
      }
    }

    function setFocus(i) {
      if (!menuItems.length) return;
      if (menuItems[currentFocusIndex]) menuItems[currentFocusIndex].classList.remove('focused');
      currentFocusIndex = Math.max(0, Math.min(i, menuItems.length - 1));
      if (menuItems[currentFocusIndex]) menuItems[currentFocusIndex].classList.add('focused');
      updateArrowVisibility();
    }

    function handleTouchStart(e) {
      touchStartX = e.changedTouches[0].screenX;
      touchStartY = e.changedTouches[0].screenY;
    }

    function handleTouchEnd(e) {
      if (!touchStartX || !touchStartY) return;
      const deltaX = e.changedTouches[0].screenX - touchStartX;
      const deltaY = e.changedTouches[0].screenY - touchStartY;
      if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > SWIPE_THRESHOLD) {
        if (deltaX < 0 && currentFocusIndex < menuItems.length - 1) {
          menuItems[currentFocusIndex + 1].focus();
        } else if (deltaX > 0 && currentFocusIndex > 0) {
          menuItems[currentFocusIndex - 1].focus();
        }
      }
      touchStartX = 0;
      touchStartY = 0;
    }

        function scrollMenu() {
      const focused = menuItems[currentFocusIndex];
      if (!focused) return;
      const containerWidth = menuScrollContainer.offsetWidth;
      const itemWidth = focused.offsetWidth + 8;
      const scrollAmt = (currentFocusIndex * itemWidth) - (containerWidth / 2) + (itemWidth / 2);
      const maxScroll = menuItemsContainer.scrollWidth - containerWidth;
      menuItemsContainer.style.transform = `translateX(-${Math.max(0, Math.min(scrollAmt, maxScroll))}px)`;
    }

    function updateArrowVisibility() {
      if (currentFocusIndex === 0) navArrowLeft.classList.remove('active');
      else navArrowLeft.classList.add('active');
      if (currentFocusIndex === menuItems.length - 1) navArrowRight.classList.remove('active');
      else navArrowRight.classList.add('active');
    }

    function launchNativeApp(pkg, label) {
      try {
        if (window.AndroidBridge && typeof window.AndroidBridge.launchApp === 'function') {
          window.AndroidBridge.launchApp(pkg);
        } else {
          console.warn('Simulating launch: ' + label);
        }
      } catch (e) { console.error('Failed to launch ' + label, e); }
    }

    // notificationOkBtn.addEventListener('click', hideNotification);
    function stopAudio() {
      const audioElements = document.querySelectorAll('audio');  // Ambil semua elemen audio di halaman
      audioElements.forEach((audio) => {
        audio.pause();  // Berhenti memutar audio
        audio.currentTime = 0;  // Mengatur ulang posisi audio ke awal
      });
    }

    // Fungsi untuk menampilkan notifikasi
    function hideNotification() {
      stopAudio(); // Hentikan semua audio yang sedang diputar
      if (notificationTimeoutId) {
        clearTimeout(notificationTimeoutId);
        notificationTimeoutId = null;
      }

      notificationPopup.classList.add('hidden');

      // Balikin fokus ke menu (atau elemen terakhir)
      if (lastFocusedElementBeforeNotif && typeof lastFocusedElementBeforeNotif.focus === 'function') {
        lastFocusedElementBeforeNotif.focus();
      } else if (menuItems.length > 0) {
        menuItems[currentFocusIndex].focus();
      }
    }



    function showNotification(payload) {
      let title = '';
      let body = '';
      let imageUrl = '';
      let soundUrl = '';

      // Cek jika payload berisi objek (notifikasi lengkap dengan gambar dan suara)
      if (payload && typeof payload === 'object') {
        title = payload.title || title;
        body = payload.body || '';
        imageUrl = payload.image_url || '';  // Ambil URL gambar
        soundUrl = payload.sound_url || '';  // Ambil URL suara
      } else {
        body = String(payload ?? '');
      }

      console.log('Image URL:', imageUrl);  // Debugging log untuk URL gambar
      console.log('Sound URL:', soundUrl);  // Debugging log untuk URL suara
      console.log('title URL:', title);  // Debugging log untuk URL gambar
      console.log('body URL:', body);  // Debugging log untuk URL suara

      const cacheKey = `${title}||${body}`;
      if (!notificationPopup.classList.contains('hidden') && lastNotificationCache === cacheKey) {
        return;
      }
      lastNotificationCache = cacheKey;

      lastFocusedElementBeforeNotif = document.activeElement;

      notificationTitle.textContent = title;
      notificationText.textContent = body;

      // Menampilkan gambar jika ada
      const notificationContent = document.getElementById('notification-text');
      if (imageUrl) {
        const imageElement = document.createElement('img');
        imageElement.src = imageUrl;  // Atur sumber gambar ke URL yang diterima
        imageElement.classList.add('w-full', 'h-auto', 'mb-4');
        notificationContent.appendChild(imageElement); // Tambahkan gambar ke dalam notifikasi
      } else {
        console.log('No image URL provided.');  // Debugging jika tidak ada gambar
      }

      // Menambahkan elemen audio untuk suara jika ada
      if (soundUrl) {
        const audioElement = document.createElement('audio');
        audioElement.src = soundUrl;  // Atur sumber audio
        audioElement.preload = 'auto';
        audioElement.loop = true;

        // Tambahkan ID unik untuk referensi
        audioElement.id = 'notification-audio';

        notificationContent.appendChild(audioElement); // Tambahkan audio ke dalam notifikasi

        // Coba play dengan promise untuk handle autoplay policy
        const playPromise = audioElement.play();

        if (playPromise !== undefined) {
          playPromise.then(() => {
            console.log('Audio playing successfully');
          }).catch(error => {
            console.warn('Autoplay was prevented, trying muted autoplay:', error);

            // Fallback: Coba play dengan muted dulu (ini biasanya diizinkan)
            audioElement.muted = true;
            audioElement.play().then(() => {
              console.log('Playing muted audio');
              // Unmute setelah dimulai
              setTimeout(() => {
                audioElement.muted = false;
              }, 100);
            }).catch(err => {
              console.error('Even muted autoplay failed:', err);
              console.log('Audio will play when user interacts (clicks OK button)');
            });
          });
        }
      } else {
        console.log('No sound URL provided.');  // Debugging jika tidak ada suara
      }

      notificationPopup.classList.remove('hidden');

      // Auto-focus ke tombol OK
      setTimeout(() => {
        notificationOkBtn.focus();

        // Coba play audio saat OK button di-focus (user interaction)
        const audioEl = document.getElementById('notification-audio');
        if (audioEl && audioEl.paused) {
          audioEl.play().catch(err => {
            console.warn('Could not play audio on focus:', err);
          });
        }
      }, 50);
    }

    // Event listener untuk tombol OK notification
    notificationOkBtn.addEventListener('click', hideNotification);





    // === Notification Polling ===
    let notificationPollingIntervalId = null;

    async function checkForNotifications() {
      if (!currentDeviceID) return;

      try {
        const res = await fetch(`./api.php?action=getNotifications&device_id=${encodeURIComponent(currentDeviceID)}&_=${Date.now()}`);
        const data = await res.json();

        if (data.status === 'success' && data.notification) {
          // data.notification boleh object {title, body} atau string
          showNotification(data.notification);
          console.log('New notification received:', data.notification);
        }
      } catch (err) {
        console.warn('Error checking notifications:', err);
      }
    }

    function startNotificationPolling() {
      if (notificationPollingIntervalId) return; // cegah double interval
      checkForNotifications(); // langsung cek sekali
      notificationPollingIntervalId = setInterval(checkForNotifications, 3000);
    }

    function stopNotificationPolling() {
      if (notificationPollingIntervalId) clearInterval(notificationPollingIntervalId);
      notificationPollingIntervalId = null;
    }




    function updateClock(weatherData) {
      const now = new Date();
      const lang = getCurrentLang();

      const timeLocale = (lang === 'en') ? 'en-US' : (lang === 'zh') ? 'zh-CN' : 'id-ID';
      timeEl.textContent = now.toLocaleTimeString(timeLocale, { hour: '2-digit', minute: '2-digit', hour12: false });
      const dateLocale = (lang === 'en') ? 'en-US' : (lang === 'zh') ? 'zh-CN' : 'id-ID';
      const dateString = now.toLocaleDateString(dateLocale, {
        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
      });

      let weatherString = '28&deg;C | Cerah';
      if (weatherData) weatherString = `${weatherData.temp}&deg;C | ${weatherData.description}`;
      dateEl.innerHTML = `${weatherString} | ${dateString}`;
    }

    // === Boot ===
    document.addEventListener('DOMContentLoaded', () => {
      const id = localStorage.getItem(STORAGE_DEVICE_ID_KEY);
      const initialLang = getCurrentLang();

      bootLoading.style.display = 'flex';
      launcherContainer.classList.add('hidden');
      disabledScreen.classList.add('hidden');

      // Always generate/retrieve registration code
      const code = getStableRegistrationCode();
      if (code) {
        if (id) {
          currentDeviceID = id;
          if (window.AndroidBridge && typeof window.AndroidBridge.setDeviceId === 'function') {
            window.AndroidBridge.setDeviceId(currentDeviceID);
          }
        } else {
          regBadge.classList.remove('hidden');
          startPolling(code);
        }
        detectLocalIP();
      }

      // Always load launcher regardless of registration
      const deviceID = id || code;
      if (deviceID) {
        currentDeviceID = deviceID;
        if (!id) {
          localStorage.setItem(STORAGE_REG_CODE_KEY, code);
        }
        loadLauncherData(deviceID, initialLang);
      } else {
        bootLoading.style.display = 'none';
        if (window.AndroidBridge && typeof window.AndroidBridge.hideLoadingScreen === 'function') {
          window.AndroidBridge.hideLoadingScreen();
        }
      }
    });

    // Placeholder Bridge
    if (typeof window.AndroidBridge === 'undefined') {
      window.AndroidBridge = {
        launchApp: (pkg) => console.log(`Simulate launchApp: ${pkg}`),
        hideLoadingScreen: () => console.log("Simulate hideLoadingScreen"),
        setDeviceId: (id) => console.log(`Simulate setDeviceId: ${id}`),
        setRoomCode: (room) => console.log(`Simulate setRoomCode: ${room}`)
      };
    }
  </script>
</body>

</html>