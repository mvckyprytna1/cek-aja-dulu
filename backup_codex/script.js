const whatsappNumber = "628xxxxxxxxxx";
const defaultWhatsAppMessage =
  "Halo Vicky, saya mau tanya tentang Cek Aja Dulu";

const header = document.querySelector("[data-header]");
const menu = document.querySelector("[data-menu]");
const menuToggle = document.querySelector("[data-menu-toggle]");
const internalLinks = document.querySelectorAll('a[href^="#"]');
const whatsappLinks = document.querySelectorAll("[data-whatsapp]");
const revealItems = document.querySelectorAll(".reveal");

const getHeaderOffset = () => (header ? header.offsetHeight + 14 : 0);

const buildWhatsAppUrl = (message = defaultWhatsAppMessage) => {
  const encodedMessage = encodeURIComponent(message);
  return `https://wa.me/${whatsappNumber}?text=${encodedMessage}`;
};

const closeMenu = () => {
  if (!menu || !menuToggle) return;

  menu.classList.remove("is-open");
  menuToggle.classList.remove("is-open");
  menuToggle.setAttribute("aria-expanded", "false");
  menuToggle.setAttribute("aria-label", "Buka menu navigasi");
  document.body.classList.remove("menu-open");
};

const openMenu = () => {
  if (!menu || !menuToggle) return;

  menu.classList.add("is-open");
  menuToggle.classList.add("is-open");
  menuToggle.setAttribute("aria-expanded", "true");
  menuToggle.setAttribute("aria-label", "Tutup menu navigasi");
  document.body.classList.add("menu-open");
};

const toggleMenu = () => {
  if (!menu) return;
  menu.classList.contains("is-open") ? closeMenu() : openMenu();
};

const scrollToTarget = (target) => {
  const top = target.getBoundingClientRect().top + window.pageYOffset - getHeaderOffset();
  window.scrollTo({
    top,
    behavior: "smooth",
  });
};

whatsappLinks.forEach((link) => {
  const message = link.dataset.message || defaultWhatsAppMessage;
  link.href = buildWhatsAppUrl(message);
  link.target = "_blank";
  link.rel = "noopener noreferrer";
});

internalLinks.forEach((link) => {
  link.addEventListener("click", (event) => {
    const hash = link.getAttribute("href");
    if (!hash || hash === "#") return;

    const target = document.querySelector(hash);
    if (!target) return;

    event.preventDefault();
    closeMenu();
    scrollToTarget(target);
    window.history.pushState(null, "", hash);
  });
});

if (menuToggle) {
  menuToggle.addEventListener("click", toggleMenu);
}

document.addEventListener("click", (event) => {
  if (!menu || !menuToggle || !menu.classList.contains("is-open")) return;
  const clickedInsideMenu = menu.contains(event.target);
  const clickedToggle = menuToggle.contains(event.target);

  if (!clickedInsideMenu && !clickedToggle) {
    closeMenu();
  }
});

document.addEventListener("keydown", (event) => {
  if (event.key === "Escape") {
    closeMenu();
  }
});

const updateHeaderState = () => {
  if (!header) return;
  header.classList.toggle("is-scrolled", window.scrollY > 24);
};

updateHeaderState();
window.addEventListener("scroll", updateHeaderState, { passive: true });

if ("IntersectionObserver" in window) {
  const revealObserver = new IntersectionObserver(
    (entries, observer) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add("is-visible");
        observer.unobserve(entry.target);
      });
    },
    {
      threshold: 0.14,
      rootMargin: "0px 0px -40px",
    }
  );

  revealItems.forEach((item) => revealObserver.observe(item));
} else {
  revealItems.forEach((item) => item.classList.add("is-visible"));
}
