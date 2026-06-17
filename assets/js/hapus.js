// assets/js/hapus.js

document.addEventListener('DOMContentLoaded', function () {
    document.body.addEventListener('click', function (e) {
        const tombolHapus = e.target.closest('.btn-hapus-custom');
        
        if (tombolHapus) {
            e.preventDefault();
            
            const urlHapus = tombolHapus.getAttribute('href');
            const namaData = tombolHapus.getAttribute('data-nama') || 'data ini';

            Swal.fire({
                title: 'Hapus data?',
                html: `Apakah Anda yakin ingin menghapus <strong style="color: #0f172a; font-weight: 600;">"${namaData}"</strong>?<br><span style="font-size: 13px; color: #64748b; display: inline-block; margin-top: 6px; font-weight: 400;">Tindakan ini permanen dan tidak dapat dibatalkan.</span>`,
                icon: 'warning',
                iconColor: '#ef4444', 
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true, 
                
                // Tema warna tombol premium (Tailwind Palette)
                background: '#ffffff',
                customClass: {
                    popup: 'custom-swal-premium',
                    title: 'custom-swal-title-fix'
                },
                buttonsStyling: true, 
                confirmButtonColor: '#ef4444', // Merah soft
                cancelButtonColor: '#94a3b8',  // Abu-abu slate biar tombol batalnya gak terlalu gelap
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = urlHapus;
                }
            });
        }
    });
});

// SUNTIK CSS UNTUK MERUBAH FONT & LENGKUNGAN BIAR SEAMLESS SAMA DASHBOARD
const style = document.createElement('style');
style.innerHTML = `
    .custom-swal-premium { 
        border-radius: 16px !important; 
        font-family: 'Inter', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important; 
    }
    .custom-swal-title-fix {
        font-family: 'Inter', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important;
        font-size: 22px !important;
        font-weight: 600 !important;
        color: #0f172a !important;
    }
    /* Merapikan font bawaan tombol SweetAlert2 */
    .swal2-styled {
        font-family: 'Inter', 'Segoe UI', Roboto, sans-serif !important;
        font-size: 14px !important;
        font-weight: 500 !important;
        padding: 10px 22px !important;
        border-radius: 8px !important;
    }
`;
document.head.appendChild(style);