const navigation = document.querySelectorAll('.bds-nav a, .bds-mobile-nav a');
const sections = [...document.querySelectorAll('main section[id]')];
const header = document.querySelector('.bds-header');
const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
let scrollMotionElements = [];
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

function updateScrollMotion() {
    const scrollableHeight = Math.max(document.documentElement.scrollHeight - window.innerHeight, 1);
    document.documentElement.style.setProperty('--bds-scroll-progress', Math.min(window.scrollY / scrollableHeight, 1));

    if (reducedMotion.matches) return;

    const mobileMultiplier = window.innerWidth <= 640 ? 0.55 : 1;
    for (const element of scrollMotionElements) {
        const bounds = element.getBoundingClientRect();
        if (bounds.bottom < -120 || bounds.top > window.innerHeight + 120) continue;

        const viewportOffset = (window.innerHeight / 2 - (bounds.top + bounds.height / 2)) / window.innerHeight;
        const strength = Number(element.dataset.scrollShift ?? 0) * mobileMultiplier;
        const shift = Math.max(-strength, Math.min(strength, viewportOffset * strength * 2));
        element.style.setProperty('--bds-scroll-shift', `${shift.toFixed(2)}px`);
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
        updateScrollMotion();
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

const scrollMotionGroups = [
    ['.bds-armada', 24],
    ['.bds-section-heading > div', 10],
    ['.bds-director blockquote', 12],
    ['.bds-director-profile', 8],
    ['.bds-centered-heading', 10],
    ['.bds-contact-copy > div', 10],
];

for (const [selector, direction] of revealGroups) {
    document.querySelectorAll(selector).forEach((element, index) => {
        element.dataset.reveal = direction;
        element.style.setProperty('--reveal-delay', `${Math.min(index * 80, 240)}ms`);
    });
}

for (const [selector, strength] of scrollMotionGroups) {
    document.querySelectorAll(selector).forEach(element => {
        element.dataset.scrollShift = strength;
        scrollMotionElements.push(element);
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

const counterObserver = new IntersectionObserver(entries => {
    for (const entry of entries) {
        if (!entry.isIntersecting) continue;

        const element = entry.target;
        const match = element.textContent.trim().match(/^(\d+)(.*)$/);
        if (!match || reducedMotion.matches) {
            counterObserver.unobserve(element);
            continue;
        }

        const target = Number(match[1]);
        const suffix = match[2];
        const startedAt = performance.now();
        const duration = 1100;

        function drawCounter(now) {
            const progress = Math.min((now - startedAt) / duration, 1);
            const easedProgress = 1 - Math.pow(1 - progress, 3);
            element.textContent = `${Math.round(target * easedProgress)}${suffix}`;
            if (progress < 1) requestAnimationFrame(drawCounter);
        }

        requestAnimationFrame(drawCounter);
        counterObserver.unobserve(element);
    }
}, { threshold: 0.55 });

document.querySelectorAll('.bds-stats strong').forEach(element => counterObserver.observe(element));
updateScrollMotion();

document.querySelector('[data-proposal-feedback]')?.scrollIntoView({ block: 'center' });
