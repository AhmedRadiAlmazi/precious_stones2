Precious Stones — Bidding Page Prototype

What this is
- A static interactive prototype (HTML/CSS/JS) demonstrating the "Luxury-Tech" bidding UX: dark theme, gold accents, particle dust, countdown, simulated live bids, and Escrow visual steps.

Files
- `index.html` — main prototype page (Arabic, RTL).
- `styles.css` — styling and animations.
- `app.js` — countdown, simulated bids, particle background, and hook for WebSocket.

How to run (Windows)
1. Open the folder `prototype/bidding` in your browser (double-click `index.html`) or serve it with a simple local static server.

Optional: serve with Python (if installed):
```powershell
cd .\prototype\bidding; python -m http.server 8000
```
Then open `http://localhost:8000` in your browser.

Integration notes
- Replace the simulated bids with real-time updates by calling `window.PROTO.pushExternalBid(name, amount)` from your WebSocket message handler.
- Hook the manual bid button to POST to `/api/auctions/{id}/bids` and handle responses/errors.
- Replace the placeholder audio base64 with a real short metal click sound file and reference it in `index.html` for better UX.

Next steps I can do
- Convert this prototype into a React component and wire it to your Laravel API.
- Create matching seller dashboard pages with the same style.
- Export high-fidelity Figma spec or CSS variables for reuse.
