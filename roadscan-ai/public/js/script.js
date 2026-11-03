/**
 * Fungsi untuk mengganti mode analisis antara Evaluasi (dengan GT) 
 * dan Deteksi Mandiri (Tanpa GT).
 */
function switchMode(mode) {
    const btnEval = document.getElementById('btn-evaluasi');
    const btnDeteksi = document.getElementById('btn-deteksi');
    const gtContainer = document.getElementById('gt-input-container');

    // Validasi apakah elemen ada untuk mencegah error console
    if (!btnEval || !btnDeteksi || !gtContainer) return;

    if (mode === 'evaluasi') {
        // Aktifkan tampilan tombol Evaluasi
        btnEval.classList.add('bg-white', 'shadow-sm', 'text-blue-600');
        btnEval.classList.remove('text-gray-500');
        
        // Matikan tampilan tombol Deteksi
        btnDeteksi.classList.remove('bg-white', 'shadow-sm', 'text-blue-600');
        btnDeteksi.classList.add('text-gray-500');
        
        // Tampilkan kembali input GT
        gtContainer.style.opacity = '1';
        gtContainer.style.pointerEvents = 'auto';
        gtContainer.style.transition = '0.3s';
    } else {
        // Aktifkan tampilan tombol Deteksi
        btnDeteksi.classList.add('bg-white', 'shadow-sm', 'text-blue-600');
        btnDeteksi.classList.remove('text-gray-500');
        
        // Matikan tampilan tombol Evaluasi
        btnEval.classList.remove('bg-white', 'shadow-sm', 'text-blue-600');
        btnEval.classList.add('text-gray-500');
        
        // Redupkan dan nonaktifkan input GT
        gtContainer.style.opacity = '0.4';
        gtContainer.style.pointerEvents = 'none';
        gtContainer.style.transition = '0.3s';
    }
}