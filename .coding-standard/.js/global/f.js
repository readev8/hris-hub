// JS INI DIGUNAKAN UNTUK MENAMPUNG GLOBAL FUNCTION UNTUK FORMATTING INPUT
$(document).ready(function () {
    $.formatNumberWithDots = function (number) {
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
})