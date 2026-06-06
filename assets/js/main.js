// ============================================
// assets/js/main.js
// JavaScript utama Campus Events
// ============================================

// --- TOGGLE SIDEBAR di Mobile ---
const btnMenu  = document.getElementById('btnMenu');
const sidebar  = document.getElementById('sidebar');
const overlay  = document.getElementById('sidebarOverlay');

if (btnMenu) {
    btnMenu.addEventListener('click', () => {
        sidebar.classList.toggle('terbuka');
        if (overlay) overlay.style.display = sidebar.classList.contains('terbuka') ? 'block' : 'none';
    });
}

if (overlay) {
    overlay.addEventListener('click', () => {
        sidebar.classList.remove('terbuka');
        overlay.style.display = 'none';
    });
}

// --- KONFIRMASI HAPUS ---
function konfirmasiHapus(url, namaItem) {
    if (confirm('⚠️ Yakin ingin menghapus "' + namaItem + '"?\n\nData yang dihapus tidak bisa dikembalikan.')) {
        window.location.href = url;
    }
}

// --- BUKA & TUTUP MODAL ---
function bukaModal(idModal) {
    const modal = document.getElementById(idModal);
    if (modal) {
        modal.classList.add('buka');
        document.body.style.overflow = 'hidden';
    }
}

function tutupModal(idModal) {
    const modal = document.getElementById(idModal);
    if (modal) {
        modal.classList.remove('buka');
        document.body.style.overflow = '';
    }
}

// Tutup modal saat klik di luar box
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('buka');
            document.body.style.overflow = '';
        }
    });
});

// --- PREVIEW GAMBAR SEBELUM UPLOAD ---
function previewGambar(inputId, previewId) {
    const input   = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    if (!input || !preview) return;

    input.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(this.files[0]);
        }
    });
}

// --- AUTO SEMBUNYIKAN ALERT SETELAH 4 DETIK ---
document.querySelectorAll('.alert').forEach(el => {
    setTimeout(() => {
        el.style.transition = 'opacity .5s';
        el.style.opacity = '0';
        setTimeout(() => el.remove(), 500);
    }, 4000);
});

// --- FILTER KATEGORI DI LANDING PAGE ---
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('aktif'));
        this.classList.add('aktif');

        const kategori = this.dataset.kategori;
        document.querySelectorAll('.event-card').forEach(card => {
            if (kategori === 'semua' || card.dataset.kategori === kategori) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    });
});

// --- TOGGLE PASSWORD VISIBILITY ---
document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', function() {
        const input = document.querySelector(this.dataset.target);
        if (input) {
            input.type = input.type === 'password' ? 'text' : 'password';
            this.textContent = input.type === 'password' ? '👁️' : '🙈';
        }
    });
});
