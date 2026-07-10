// FILE INI DIGUNAKAN UNTUK CLEARING / RESET ELEMENT

$.clearForm = function (id) {
    var form = $('#' + id);
    form.find('input[type=text]').each(function (index, element) {
        $(element).val('');
    });
    form.find('.select2').each(function (index, element) {
        $(element).val('').trigger('change');
    });
};