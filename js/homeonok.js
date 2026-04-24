
document.addEventListener("DOMContentLoaded", () => {
  document.addEventListener("keydown", (e) => {
  
    if (e.key === "Enter" || e.key === "OK") {
   
      if (document.getElementById("returnOverlay")) return;

 
      const overlay = document.createElement("div");
      overlay.id = "returnOverlay";
      overlay.style = `
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.85);
        backdrop-filter: blur(6px);
        color: white;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-family: 'Inter', sans-serif;
        z-index: 9999;
        opacity: 0;
        transition: opacity 0.5s ease-in-out;
      `;
      overlay.innerHTML = `
        <div style="animation: pulse 1s infinite; text-align:center;">
          <div style="font-size:3rem;">🏨</div>
          <div>Returning to Home...</div>
        </div>
        <style>
          @keyframes pulse {
            0% { opacity: 0.6; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.1); }
            100% { opacity: 0.6; transform: scale(1); }
          }
        </style>
      `;
      document.body.appendChild(overlay);
      setTimeout(() => { overlay.style.opacity = "1"; }, 10);

      // Setelah 1 detik, arahkan ke launcher
      setTimeout(() => {
        window.location.href = "index.php";
      }, 1000);
    }
  });
});