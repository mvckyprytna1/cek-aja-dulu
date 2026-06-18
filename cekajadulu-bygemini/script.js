/**
 * PENGATURAN WHATSAPP
 * Ganti nomor di bawah ini dengan nomor tujuan.
 * Format: gunakan kode negara (misal 62) tanpa tanda plus (+) atau angka 0 di depan.
 */
const WA_NUMBER = "6281234567890";

document.addEventListener("DOMContentLoaded", () => {
    
    // 1. KONTROL NAVBAR SCROLL & BLUR
    const navbar = document.getElementById("navbar");
    const headerOffset = 72; // Sesuai var(--nav-height)

    window.addEventListener("scroll", () => {
        if (window.scrollY > 20) {
            navbar.classList.add("scrolled");
        } else {
            navbar.classList.remove("scrolled");
        }
    });

    // 2. KONTROL MENU MOBILE (HAMBURGER)
    const hamburger = document.getElementById("hamburger");
    const navMenu = document.getElementById("nav-menu");
    const navLinks = document.querySelectorAll(".nav-link");

    function toggleMenu() {
        hamburger.classList.toggle("active");
        navMenu.classList.toggle("active");
        
        // Update Aksesibilitas
        const isExpanded = hamburger.classList.contains("active");
        hamburger.setAttribute("aria-expanded", isExpanded);
    }

    hamburger.addEventListener("click", toggleMenu);

    // Tutup menu saat link diklik
    navLinks.forEach(link => {
        link.addEventListener("click", () => {
            if (navMenu.classList.contains("active")) {
                toggleMenu();
            }
        });
    });

    // Tutup menu saat klik di luar area menu
    document.addEventListener("click", (e) => {
        if (!hamburger.contains(e.target) && !navMenu.contains(e.target) && navMenu.classList.contains("active")) {
            toggleMenu();
        }
    });

    // 3. SMOOTH SCROLL DENGAN OFFSET (Supaya konten tidak tertutup header)
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    
    anchorLinks.forEach(anchor => {
        anchor.addEventListener("click", function (e) {
            const targetId = this.getAttribute("href");
            
            // Bypass jika hanya "#"
            if (targetId === "#") return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                e.preventDefault();
                const elementPosition = targetElement.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.scrollY - headerOffset;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: "smooth"
                });
            }
        });
    });

    // 4. DINAMISASI TOMBOL WHATSAPP
    const waButtons = document.querySelectorAll(".btn-wa");
    
    waButtons.forEach(button => {
        button.addEventListener("click", (e) => {
            e.preventDefault();
            // Ambil custom message dari attribute data-message, atau set default
            let customMessage = button.getAttribute("data-message") || "Halo Vicky, saya mau tanya tentang Cek Aja Dulu.";
            
            // Format URL WhatsApp
            const encodedMessage = encodeURIComponent(customMessage);
            const waUrl = `https://wa.me/${WA_NUMBER}?text=${encodedMessage}`;
            
            // Buka di tab baru
            window.open(waUrl, '_blank');
        });
    });

    // 5. INTERSECTION OBSERVER (SCROLL REVEAL ANIMATION RINGAN)
    const revealElements = document.querySelectorAll(".reveal");

    const revealOptions = {
        threshold: 0.15,
        rootMargin: "0px 0px -50px 0px"
    };

    const revealOnScroll = new IntersectionObserver(function(entries, observer) {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add("active");
            // Unobserve setelah animasi selesai agar ringan
            observer.unobserve(entry.target);
        });
    }, revealOptions);

    revealElements.forEach(el => {
        revealOnScroll.observe(el);
    });

});