const exportTahunSelect = document.getElementById('export-tahun-select');
const exportBulanSelect = document.getElementById('export-bulan-select');
const exportFormatCard = document.getElementById('export-format-card');

exportBulanSelect.addEventListener('change', function () {
    exportFormatCard.style.display = 'none';
})

exportTahunSelect.addEventListener('change', function () {
    exportFormatCard.style.display = 'none';
})
