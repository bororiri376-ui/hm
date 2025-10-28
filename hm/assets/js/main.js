// JS init
console.log('HIMASI site loaded');

// Bubble hover effect for buttons
(function () {
  function setPos(el, x, y) {
    el.style.setProperty('--x', x + 'px');
    el.style.setProperty('--y', y + 'px');
  }

  function handleMove(e) {
    const el = e.currentTarget;
    const rect = el.getBoundingClientRect();
    const x = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left;
    const y = (e.touches ? e.touches[0].clientY : e.clientY) - rect.top;
    setPos(el, x, y);
  }

  function handleEnter(e) {
    e.currentTarget.classList.add('bubble-active');
  }
  function handleLeave(e) {
    e.currentTarget.classList.remove('bubble-active');
  }

  function bind(el) {
    el.addEventListener('mousemove', handleMove, { passive: true });
    el.addEventListener('mouseenter', handleEnter, { passive: true });
    el.addEventListener('mouseleave', handleLeave, { passive: true });
    el.addEventListener('touchstart', handleMove, { passive: true });
    el.addEventListener('touchmove', handleMove, { passive: true });
    el.addEventListener('touchend', handleLeave, { passive: true });
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn').forEach(bind);
    // Navbar bubble effect
    document.querySelectorAll('.navbar').forEach(function (nav) {
      function setNavPos(e) {
        const rect = nav.getBoundingClientRect();
        const x = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left;
        const y = (e.touches ? e.touches[0].clientY : e.clientY) - rect.top;
        nav.style.setProperty('--x', x + 'px');
        nav.style.setProperty('--y', y + 'px');
      }
      nav.addEventListener('mousemove', setNavPos, { passive: true });
      nav.addEventListener('touchstart', setNavPos, { passive: true });
      nav.addEventListener('touchmove', setNavPos, { passive: true });
    });
  });
})();
