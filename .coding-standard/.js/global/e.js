// FILE INI DIGUNAKAN UNTUK MENAMPUNG GLOBAL FUNCTION UNTUK ERROR HANDLING

$(document).on("input", ".input-only-numeric", function (e) {
    const input = e.target;
    const value = input.value;

    // Remove any character that is not a digit or a dot
    const sanitizedValue = value.replace(/[^0-9\.]/g, '');

    // Update the input field only if the value has changed
    if (sanitizedValue !== value) {
        input.value = sanitizedValue;
    }
});
$(document).ready(function () {
    // FUNGSI INI DIGUNAKAN UNTUK CHECKING DATA KOSONG 
    // FORMAT FIELD {
    //     'field_name' : 'Pesan Error'
    // }
    $.emptyCheck = function (field) {
        var error = {};
        $.each(field, function (key, value) {
            if ($(`#${key}`).val() == '' || $(`#${key}`).val() == null) {
                error[key] = value;
            }
        });
        return error;

    }
})

