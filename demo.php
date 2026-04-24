<?php
include 'config.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <meta name="mobile-web-app-capable" content="yes">
  <meta http-equiv="Feature-Policy" content="autoplay 'self'">
  <title>Hotel TV Launcher - Demo</title>
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
      0% { transform: translateX(0%); }
      100% { transform: translateX(-100%); }
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

    .demo-badge {
      position: fixed;
      top: 10px;
      right: 10px;
      background: #facc15;
      color: #111;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 700;
      z-index: 9999;
    }

    *:focus {
      outline: none !important;
      border: none !important;
      box-shadow: none !important;
    }
  </style>
</head>

<body class="bg-gray-900 text-white h-screen w-screen overflow-hidden">

  <div class="demo-badge">DEMO MODE</div>

  <div id="launcher-container" class="relative h-full w-full bg-launcher">
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
            <p class="guest-name" id="guest-name-display">Demo Guest</p>
          </div>
          <div class="guest-avatar">
            <svg fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
            </svg>
          </div>
          <div class="guest-room-wrapper text-right">
            <p class="room-label">Room</p>
            <p class="room-number" id="room-number-display">999</p>
          </div>
        </div>
      </header>

      <div class="flex-grow"></div>

      <footer class="menu-container-wrapper">
        <div id="nav-arrow-left" class="nav-arrow p-2">
          <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
          </svg>
        </div>
        <div class="menu-scroll-container">
          <div id="main-menu-items"></div>
        </div>
        <div id="nav-arrow-right" class="nav-arrow active p-2">
          <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
          </svg>
        </div>
      </footer>
    </div>

    <div class="marquee-container">
      <div class="marquee-text" id="marquee-text-content">Welcome to Hotel TV Demo</div>
    </div>
  </div>

  <script>
    const PAGE_ROUTES = {
      information: 'information.html?v=17',
      general_info: 'general_info.html?v=13',
      general_information: 'general_info.html?v=13',
      facilities: 'facilities.html?v=6',
      amenities: 'amenities.html?v=14',
      dining: 'dining.html?v=9',
      promotion: 'promotion.html?v=7'
    };

    const DEFAULT_FOCUS_KEY = 'facilities';

    let menuItems = [];
    let currentFocusIndex = 0;

    const menuItemsContainer = document.getElementById('main-menu-items');
    const menuScrollContainer = document.querySelector('.menu-scroll-container');
    const timeEl = document.getElementById('time-display');
    const dateEl = document.getElementById('date-display');
    const weatherIconEl = document.getElementById('weather-icon-display');
    const marqueeText = document.getElementById('marquee-text-content');
    const navArrowLeft = document.getElementById('nav-arrow-left');
    const navArrowRight = document.getElementById('nav-arrow-right');

    function getWeatherIcon(iconCode) {
      return `https://openweathermap.org/img/wn/${iconCode}@2x.png`;
    }

    async function loadLauncherData() {
      try {
        const [marqueeRes, appsRes, weatherRes] = await Promise.all([
          fetch(`./api.php?action=getMarqueeText&lang=id&_=${Date.now()}`),
          fetch(`./api.php?action=getAppVisibility&lang=id&_=${Date.now()}`),
          fetch(`./api.php?action=getWeather&lang=id&_=${Date.now()}`)
        ]);

        const marqueeData = marqueeRes.ok ? await marqueeRes.json() : {};
        if (marqueeData.status === 'success') {
          marqueeText.textContent = marqueeData.text || 'Welcome to Hotel TV Demo';
        }

        const appsData = appsRes.ok ? await appsRes.json() : {};
        if (appsData.status === 'success' && appsData.apps) {
          buildMainMenu(appsData.apps);
          const idx = appsData.apps.findIndex(a => a.app_key === DEFAULT_FOCUS_KEY);
          if (idx !== -1) currentFocusIndex = idx;
        }

        const weatherData = weatherRes.ok ? await weatherRes.json() : {};
        let weatherInfo = null;
        if (weatherData.status === 'success') {
          weatherInfo = weatherData.data;
          weatherIconEl.src = getWeatherIcon(weatherData.data.icon);
          weatherIconEl.classList.remove('hidden');
        }

        setTimeout(() => {
          if (menuItems[currentFocusIndex]) menuItems[currentFocusIndex].classList.add('focused');
          scrollMenu();
        }, 100);

        document.addEventListener('keydown', handleKeyDown);
        updateClock(weatherInfo);
        setInterval(() => updateClock(weatherInfo), 1000);
        loadDynamicBackground();

      } catch (err) {
        console.error('Error loading demo data:', err);
      }
    }

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

    function buildMainMenu(apps) {
      menuItemsContainer.innerHTML = '';
      apps.forEach((app, i) => {
        const el = document.createElement('div');
        el.className = 'menu-item';
        el.tabIndex = 0;
        el.dataset.index = i;
        el.dataset.page = PAGE_ROUTES[app.app_key] || '';
        el.dataset.pkg = app.android_package || '';
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
        window.location.href = item.dataset.page;
      }
      else if (item.dataset.pkg) {
        console.warn('Demo mode: native app launch simulated for', item.dataset.label);
      }
    }

    function handleKeyDown(e) {
      const key = e.key || '';

      if (key === 'Back' || e.keyCode === 4) {
        e.preventDefault();
        return;
      }

      const blocked = ['MetaLeft', 'MetaRight', 'Home', 'Escape', 'F10'];
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

    function updateClock(weatherData) {
      const now = new Date();
      timeEl.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false });
      const dateString = now.toLocaleDateString('id-ID', {
        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
      });
      let weatherString = '28&deg;C | Cerah';
      if (weatherData) weatherString = `${weatherData.temp}&deg;C | ${weatherData.description}`;
      dateEl.innerHTML = `${weatherString} | ${dateString}`;
    }

    document.addEventListener('DOMContentLoaded', () => {
      loadLauncherData();
    });
  </script>
</body>

</html>