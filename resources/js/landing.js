const navigation = document.querySelectorAll('.bds-nav a, .bds-mobile-nav a');
const sections = [...document.querySelectorAll('main section[id]')];
const header = document.querySelector('.bds-header');
let lastScrollY = window.scrollY;
let headerTimer;

function updateNavigation() {
    const active = sections.filter(section => section.getBoundingClientRect().top <= 180).at(-1) ?? sections[0];
    for (const link of navigation) {
        const mappedSections = link.dataset.sections?.split(' ') ?? [];
        if (link.hash === `#${active.id}` || mappedSections.includes(active.id)) link.setAttribute('aria-current', 'location');
        else link.removeAttribute('aria-current');
    }
}

let scheduled = false;
window.addEventListener('scroll', () => {
    if (scheduled) return;
    scheduled = true;
    requestAnimationFrame(() => {
        const currentScrollY = Math.max(window.scrollY, 0);
        const movingDown = currentScrollY > lastScrollY + 4;
        const movingUp = currentScrollY < lastScrollY - 4;

        header.classList.toggle('is-scrolled', currentScrollY > 20);
        if (currentScrollY < 80 || movingUp) header.classList.remove('is-hidden');
        else if (movingDown) header.classList.add('is-hidden');

        lastScrollY = currentScrollY;
        updateNavigation();
        scheduled = false;
    });
}, { passive: true });
updateNavigation();

window.addEventListener('pointermove', event => {
    if (event.clientY > 88 || window.scrollY < 80) return;
    header.classList.remove('is-hidden');
    clearTimeout(headerTimer);
    headerTimer = setTimeout(() => {
        if (window.scrollY > 80) header.classList.add('is-hidden');
    }, 1800);
}, { passive: true });

const revealGroups = [
    ['.bds-hero-copy', 'left'],
    ['.bds-hero-visual', 'right'],
    ['.bds-stats > div', 'up'],
    ['.bds-section-heading', 'up'],
    ['.bds-service-card', 'up'],
    ['.bds-director > *', 'up'],
    ['.bds-centered-heading', 'up'],
    ['.bds-partner', 'up'],
    ['.bds-standards .bds-section-heading', 'up'],
    ['.bds-standard-grid article', 'up'],
    ['.bds-contact-copy', 'left'],
    ['.bds-contact-form', 'right'],
    ['.bds-footer-grid > *', 'up'],
];

for (const [selector, direction] of revealGroups) {
    document.querySelectorAll(selector).forEach((element, index) => {
        element.dataset.reveal = direction;
        element.style.setProperty('--reveal-delay', `${Math.min(index * 80, 240)}ms`);
    });
}

document.body.classList.add('bds-motion-ready');
const revealObserver = new IntersectionObserver(entries => {
    for (const entry of entries) {
        if (!entry.isIntersecting) continue;
        entry.target.classList.add('is-visible');
        revealObserver.unobserve(entry.target);
    }
}, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });

document.querySelectorAll('[data-reveal]').forEach(element => revealObserver.observe(element));

document.querySelector('[data-proposal-feedback]')?.scrollIntoView({ block: 'center' });
