const links = [...document.querySelectorAll('.desktop-nav a, .mobile-dock a')];
const sections = [...document.querySelectorAll('main section[id]')];
if ('IntersectionObserver' in window && links.length) {
  const observer = new IntersectionObserver(entries => {
    const active = entries.filter(entry => entry.isIntersecting).sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];
    if (active) links.forEach(link => link.classList.toggle('active', link.hash === `#${active.target.id}`));
  }, { rootMargin: '-25% 0px -60% 0px', threshold: [0, 0.25, 0.5] });
  sections.forEach(section => observer.observe(section));
}
