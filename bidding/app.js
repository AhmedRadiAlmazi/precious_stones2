// bidding prototype JS: countdown, simulated live bids, particles, UI hooks
// This script can be used on auction pages to demonstrate live bidding UX.
// It exposes window.PROTO.pushExternalBid(name, amount) for server-driven updates.

// find key elements if present on the page
const countdownEl = document.getElementById('countdown-timer');
const bidsList = document.getElementById('bids-list');
const feed = document.getElementById('feed');
const bidBtn = document.getElementById('bid-btn');
const bidAmountInput = document.getElementById('bid-amount');
const youTop = document.getElementById('you-top');
const metalClick = document.getElementById('metal-click');

// lightweight guard: only run if page has the bidding elements
if (countdownEl && bidsList && feed) {
  // load small click sound placeholder (recommended: replace with real file)
  if (metalClick) metalClick.src = 'data:audio/mpeg;base64,//uQZAAAAAAAAAAAAAAAAAAAAAAAWGluZwAAAA8AAAACAAACcQCA...';

  // Countdown (start 5:00)
  let remaining = 5 * 60; // seconds
  const formatTime = s => {
    const m = String(Math.floor(s/60)).padStart(2,'0');
    const sec = String(s%60).padStart(2,'0');
    return `${m}:${sec}`;
  };
  countdownEl.textContent = formatTime(remaining);

  const timerInterval = setInterval(()=>{
    remaining -= 1;
    if(remaining <= 0){
      clearInterval(timerInterval);
      countdownEl.textContent = 'انتهى';
      const status = document.querySelector('.status'); if(status) status.textContent = 'انتهى المزاد';
      if(bidBtn){ bidBtn.disabled = true; bidBtn.style.opacity = 0.6; }
      return;
    }
    countdownEl.textContent = formatTime(remaining);
    if(remaining <= 300){
      countdownEl.classList.add('urgent');
      countdownEl.style.background = 'linear-gradient(90deg, rgba(255,165,0,0.14), rgba(202,163,74,0.04))';
    }
  },1000);

  // Simulated live bids
  const users = ['ليلى','يوسف','سارة','علي','محمد','أحمد','فاطمة'];
  let currentPrice = Number(document.querySelector('.price')?.textContent?.replace(/[^0-9\.]/g,'') || 12500);

  function pushBid(name, amount){
    const li = document.createElement('li');
    li.innerHTML = `<strong>${name}</strong> — $${amount.toLocaleString()} · <span class="time">الآن</span>`;
    bidsList.insertBefore(li, bidsList.firstChild);
    // update price
    const priceEl = document.querySelector('.price'); if(priceEl) priceEl.textContent = `$${amount.toLocaleString()}`;
    // add feed item
    const f = document.createElement('div'); f.className='item'; f.textContent = `${name} زيادة إلى $${amount.toLocaleString()}`;
    feed.insertBefore(f, feed.firstChild);
    // play sound
    try{ if(metalClick){ metalClick.currentTime = 0; metalClick.play(); } }catch(e){}
    // golden flash
    const gallery = document.querySelector('.gallery');
    if(gallery){
      gallery.animate([{boxShadow:'0 0 0px rgba(202,163,74,0)'},{boxShadow:'0 12px 60px rgba(202,163,74,0.12)'}],{duration:420,iterations:1});
    }
  }

  let simTimeout;
  function scheduleSim(){
    const wait = 3000 + Math.random()*7000;
    simTimeout = setTimeout(()=>{
      const inc = Math.floor((50 + Math.random()*300)/10)*10;
      currentPrice += inc;
      const user = users[Math.floor(Math.random()*users.length)];
      pushBid(user, currentPrice);
      scheduleSim();
    }, wait);
  }
  scheduleSim();

  // manual bid action
  if(bidBtn){
    bidBtn.addEventListener('click', ()=>{
      const val = Number(bidAmountInput?.value) || (currentPrice + 50);
      if(val <= currentPrice){ alert('يجب أن تكون المزايدة أعلى من السعر الحالي'); return; }
      pushBid('أنت', val);
      youTop?.classList.remove('hidden');
      setTimeout(()=>youTop?.classList.add('hidden'), 3500);
      currentPrice = val;
      // hook point: send /api/auctions/{id}/bids
    });
  }

  // Particles background (gold dust) simple
  const canvas = document.getElementById('particles');
  if(canvas){
    const ctx = canvas.getContext('2d');
    function resize(){canvas.width = canvas.offsetWidth; canvas.height = canvas.offsetHeight}
    window.addEventListener('resize', resize); resize();
    const particles = [];
    for(let i=0;i<60;i++) particles.push({x:Math.random()*canvas.width,y:Math.random()*canvas.height,r:Math.random()*1.4+0.4, vx:(Math.random()-0.5)*0.15, vy:-0.05 - Math.random()*0.2, alpha:0.2+Math.random()*0.6});
    function draw(){
      ctx.clearRect(0,0,canvas.width,canvas.height);
      for(const p of particles){
        p.x += p.vx; p.y += p.vy;
        if(p.y < -10){p.y = canvas.height + 10; p.x = Math.random()*canvas.width}
        if(p.x < -10) p.x = canvas.width + 10; if(p.x > canvas.width+10) p.x = -10;
        ctx.beginPath(); ctx.fillStyle = `rgba(202,163,74,${p.alpha})`; ctx.arc(p.x,p.y,p.r,0,Math.PI*2); ctx.fill();
      }
      requestAnimationFrame(draw);
    }
    draw();
  }

  window.addEventListener('beforeunload', ()=>{ clearTimeout(simTimeout); });

  // Expose hooks for WebSocket integration (example)
  window.PROTO = {
    pushExternalBid: (name, amount)=>{ currentPrice = amount; pushBid(name, amount);} 
  };
}