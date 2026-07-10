$(document).ready(function () {
    $.getUrlFilter = function () {
        var urlParams = new URLSearchParams(window.location.search);
        var filter = urlParams.get('f');
        return filter;
    }

    $.getToken = function () {
        var urlParams = new URLSearchParams(window.location.search);
        var filter = urlParams.get('token');
        return filter;
    }
    $.back = function (url) {

        var current_url = "";
        if (url == null || url == '') {
            url = new URL(window.location.href).pathname;
        }
        current_url = url.split('?')[0];
        if ($.getUrlFilter() == null || $.getUrlFilter() == '') { window.location.href = current_url; return; }

        $.post(`${site_url}helper/gf`, { filter: $.getUrlFilter() }, (res) => {
            // window.location.href = (res.statuscode != 200) ? current_url : `${res.filter.url}?f=${res.filter.id}`;
            if (res.statuscode != 200) return;

            var url = res.filter.url.split('?')[0];
            window.location.href = (res.statuscode != 200) ? current_url : `${url}?f=${res.filter.id}`;

        });
    }
})