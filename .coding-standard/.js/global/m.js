// FILE INI DIGUNAKAN UNTUK MENAMPUNG GLOBAL FUNCTION UNTUK MASKING INPUT

$(document).ready(function () {
    function formatToCurrency(amount) {
        // Pastikan amount adalah angka yang valid sebelum diformat
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 2,
        }).format(amount);
    }
    $.formatToCurrency = function (id) {
        const inputElement = $(`#${id}`);
        inputElement.addEventListener('input', function (e) {
            // 1. Ambil nilai dari input dan bersihkan dari karakter non-digit dan koma desimal
            let value = e.target.value;
            // Hapus semua karakter non-digit kecuali koma dan titik desimal
            value = value.replace(/[^0-9,.]/g, '');
            // Ganti koma dengan titik untuk konversi ke float
            value = value.replace(',', '.');

            // 2. Konversi nilai menjadi angka
            const numericValue = parseFloat(value);

            // 3. Format angka jika valid, jika tidak, biarkan kosong
            if (!isNaN(numericValue)) {
                // Gunakan Intl.NumberFormat untuk mendapatkan string yang terformat
                const formatter = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 2,
                });

                // 4. Update nilai di textbox dengan format baru
                // Simpan posisi kursor untuk mempertahankan pengalaman pengguna
                const cursorPosition = e.target.selectionStart;
                const formatted = formatter.format(numericValue);
                e.target.value = formatted;

                // Atur kembali posisi kursor
                // Ini adalah langkah opsional tapi sangat disarankan untuk user experience yang lebih baik
                // const newCursorPosition = cursorPosition + (formatted.length - value.length);
                // e.target.setSelectionRange(newCursorPosition, newCursorPosition);

            } else {
                // Jika input tidak valid (misalnya, hanya "Rp"), kosongkan
                e.target.value = '';
            }
        });
    }
    $.maskingToNumber = function (id) {
        const maskedInput = document.getElementById(id);
        maskedInput.addEventListener('input', function (e) {

            // Ambil nilai input
            let inputValue = e.target.value;

            // Format nilai dan setel kembali ke input
            e.target.value = formatNumberWithDots(inputValue);
        });

    }
    function formatNumberWithDots(number) {
        // Hapus semua karakter yang bukan digit
        let cleaned = ('' + number).replace(/\D/g, '');

        // Jika tidak ada digit, kembalikan string kosong
        if (!cleaned) {
            return '';
        }

        // Tambahkan titik sebagai pemisah ribuan
        let formatted = cleaned.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

        return formatted;
    }
});