let result = {};

$.secureAjax = function (options) {
    // Default options
    var defaults = {
        type: 'GET',
        url: '',
        data: {},
        headers: {},
        success: function () { },
        error: function () { }
    };
    var xhr = new XMLHttpRequest();
    xhr.open('GET', `${site_url}/request/get`, true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.send();

    xhr.onload = function () {
        if (xhr.status === 200) {
            var data = JSON.parse(xhr.response);
            options = $.extend(defaults, options);

            $.ajax({
                type: options.type,
                url: options.url,
                data: options.data,
                dataType: options.dataType,
                headers: {
                    'X-CSRF-TOKEN': data.token[$("#i").val()]
                },
                success: function (data) {
                    options.success(data);
                    return data;

                },
                error: function (xhr, status, error) {
                    // Panggil callback error
                    options.error(xhr, status, error);
                    return null;
                }
            });
        } else {
            toastr.error("Gagal memperbarui token keamanan. Silakan refresh halaman.", "Error");
        }
    };

};
