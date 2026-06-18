document.addEventListener("DOMContentLoaded", () => {
    let propertiDatabase = [];
    const namaFiturAktif = 'jasa'; 

    // ============================================
    // HAMBURGER MENU HANDLER - TAMBAHAN
    // ============================================
    const hamburger = document.getElementById("hamburger");
    const navMenu = document.getElementById("nav-menu");
    
    if (hamburger && navMenu) {
        hamburger.addEventListener("click", () => {
            hamburger.classList.toggle("active");
            navMenu.classList.toggle("active");
        });

        // Tutup menu saat nav link diklik
        const navLinks = navMenu.querySelectorAll(".nav-link");
        navLinks.forEach(link => {
            link.addEventListener("click", () => {
                hamburger.classList.remove("active");
                navMenu.classList.remove("active");
            });
        });
    }

    const ambilDataDariMySQL = async () => {
        try {
            // Memanggil API dengan parameter fitur murni properti
            const respon = await fetch("../admin/api.php?fitur=jasa");
            if (!respon.ok) throw new Error("Server mengembalikan status: " + respon.status);

            const teksRespon = await respon.text();
            let hasilJson;
            try {
                hasilJson = JSON.parse(teksRespon);
            } catch (e) {
                console.error("Respon server bukan JSON valid:", teksRespon);
                throw new Error("Format data dari server rusak.");
            }
            
            if (hasilJson && hasilJson.status === "success" && Array.isArray(hasilJson.data)) {
                jasaDatabase = hasilJson.data;
            } else {
                throw new Error("Struktur data JSON tidak dikenali.");
            }
            
            filterAndSortData();
        } catch (error) {
            console.error("Gagal memuat data properti:", error);
            const jasaGridHTML = document.getElementById("jasa-grid-container");
            if (jasaGridHTML) {
                jasaGridHTML.innerHTML = `<p style="grid-column: 1/-1; text-align: center; color: red; padding: 40px 0; font-weight: bold;">Gagal memuat data dari database server cPanel.</p>`;
            }
        }
    };

    const setupWaLinks = () => {
        const waButtons = document.querySelectorAll(".btn-wa");
        waButtons.forEach(btn => {
            btn.onclick = (e) => {
                e.preventDefault();
                const dbPhone = btn.getAttribute("data-phone");
                const message = btn.getAttribute("data-message") || "Halo, saya ingin bertanya tentang jasa dan layanan ini.";
                let nomorWaTarget = (!dbPhone || dbPhone.trim() === "") ? "6281252580812" : dbPhone;
                nomorWaTarget = nomorWaTarget.replace(/[^0-9]/g, "");
                if (nomorWaTarget.startsWith("0")) {
                    nomorWaTarget = "62" + nomorWaTarget.slice(1);
                }
                window.open(`https://wa.me/${nomorWaTarget}?text=${encodeURIComponent(message)}`, "_blank");
            };
        });
    };
    
    const jasaGrid = document.getElementById("jasa-grid-container");
    const searchInput = document.getElementById("search-input");
    const filterKategori = document.getElementById("filter-kategori");
    const filterUrutkan = document.getElementById("filter-urutkan");

    const renderJasa = (data) => {
        if (!jasaGrid) return;
        jasaGrid.innerHTML = "";

        const existingBtn = document.getElementById("btn-lihat-selengkapnya");
        if (existingBtn) existingBtn.remove();

        if (!data || data.length === 0) {
            jasaGrid.innerHTML = `<p style="grid-column: 1/-1; text-align: center; color: #666; padding: 40px 0;">Jasa dan Layanan tidak ditemukan.</p>`;
            return;
        }

        let tampilkanSemua = false;
        const jalankanRenderGrid = (listData) => {
            jasaGrid.innerHTML = "";
            listData.forEach(item => {
                const specSpans = item.specs ? item.specs.split("|").map(spec => `<span>${spec.trim()}</span>`).join("") : "";
                const periodHTML = item.price_period ? ` <span class="price-period">${item.price_period}</span>` : "";
                let jalurGambar = (!item.image_type || item.image_type.trim() === "" || item.image_type === "file") ? "https://placehold.co/600x400?text=Gambar+Jasa" : `../uploads/${item.image_type}`;

                const cardHTML = `
                    <article class="jasa-card">
                        <div class="card-image-wrapper" style="height: 200px; background: #e2e8f0; overflow: hidden; position: relative;">
                            <img src="${jalurGambar}" alt="${item.title || ''}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='https://placehold.co/600x400?text=Gambar+Error';">
                            <span class="card-badge ${item.status_class || 'status-tersedia'}">${item.status || 'Tersedia'}</span>
                        </div>
                        <div class="card-content">
                            <h3 class="card-title">${item.title || 'Tanpa Judul'}</h3>
                            <p class="card-location">📍 ${item.location || 'Lokasi Belum Diisi'}</p>
                            <h4 class="card-price">${item.price_display || 'Hubungi Kontak'}${periodHTML}</h4>
                            <div class="card-specs">${specSpans}</div>
                            <div class="card-actions">
                                <a href="jasa_detail.html?id=${item.id}&fitur=${namaFiturAktif}" class="btn btn-sm btn-outline" style="text-decoration: none; text-align: center; display: inline-flex; align-items: center; justify-content: center;">Cek Detail</a>
                                <button class="btn btn-sm btn-primary btn-wa" data-message="${item.wa_message || ''}" data-phone="${item.no_wa || ''}">Tanya Dulu</button>
                            </div>
                        </div>
                    </article>
                `;
                jasaGrid.insertAdjacentHTML("beforeend", cardHTML);
            });
            setupWaLinks();
        };

        const dataTerbatas = data.slice(0, 3);
        jalankanRenderGrid(dataTerbatas);

        if (data.length > 3) {
            const btnLihatSelengkapnya = document.createElement("button");
            btnLihatSelengkapnya.id = "btn-lihat-selengkapnya";
            btnLihatSelengkapnya.innerText = "Lihat Selengkapnya";
            btnLihatSelengkapnya.style.cssText = `display: block; margin: 30px auto 0 auto; padding: 12px 30px; background-color: #1e3a8a; color: #ffffff; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: background 0.3s ease;`;

            btnLihatSelengkapnya.onclick = () => {
                tampilkanSemua = !tampilkanSemua;
                if (tampilkanSemua) {
                    jalankanRenderGrid(data);
                    btnLihatSelengkapnya.innerText = "Sembunyikan";
                } else {
                    jalankanRenderGrid(dataTerbatas);
                    btnLihatSelengkapnya.innerText = "Lihat Selengkapnya";
                }
            };
            jasaGrid.parentNode.insertBefore(btnLihatSelengkapnya, jasaGrid.nextSibling);
        }
    };

    const filterAndSortData = () => {
        const keyword = searchInput ? searchInput.value.toLowerCase() : "";
        const kategori = filterKategori ? filterKategori.value : "semua";
        const urutan = filterUrutkan ? filterUrutkan.value : "terbaru";
        let hasil = [...jasaDatabase];

        if (keyword) {
            hasil = hasil.filter(item => (item.title && item.title.toLowerCase().includes(keyword)));
        }
        if (kategori !== "semua") {
            hasil = hasil.filter(item => item.category && item.category.toLowerCase() === kategori.toLowerCase());
        }
        if (urutan === "termurah") {
            hasil.sort((a, b) => (Number(a.price) || 0) - (Number(b.price) || 0));
        } else if (urutan === "termahal") {
            hasil.sort((a, b) => (Number(b.price) || 0) - (Number(a.price) || 0));
        } else {
            hasil.sort((a, b) => (Number(b.id) || 0) - (Number(a.id) || 0));
        }
        renderJasa(hasil);
    };

    if (searchInput) searchInput.addEventListener("input", filterAndSortData);
    if (filterKategori) filterKategori.addEventListener("change", filterAndSortData);
    if (filterUrutkan) filterUrutkan.addEventListener("change", filterAndSortData);

    // SCROLL KE KATALOG
    const btnScrollKatalog = document.getElementById("btn-scroll-katalog");
    if (btnScrollKatalog) {
        btnScrollKatalog.addEventListener("click", () => {
            const target = document.getElementById("katalog-jasa");
            if (target) {
                const navbarHeight = document.getElementById("navbar")?.offsetHeight || 70;
                const offsetPosition = target.getBoundingClientRect().top + window.pageYOffset - navbarHeight - 16;
                window.scrollTo({ top: offsetPosition, behavior: "smooth" });
            }
        });
    }

    ambilDataDariMySQL();
});