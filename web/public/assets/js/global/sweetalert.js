$(document).ready(function () {
  window.swal_success_redirect = function (url) {
    Swal.fire({
      title: "Success",
      text: "Data telah tersimpan",
      type: "success",
      timer: 6000,
      willClose: function () {
        window.location.href = url;
      },
    });
  };
  $.swal_error = function (text) {
    Swal.fire({
      title: "Error",
      text: text,
      type: "error",
    });
  }
  $.swal_error_redirect = function (url, text) {
    Swal.fire({
      title: "Error",
      text: text,
      type: "error",
      willClose: function () {
        window.location.href = url;
      },
    });
  }
  $.swal_redirect = function (url, text) {
    Swal.fire({
      title: "Success",
      text: text,
      type: "success",
      timer: 6000,
      willClose: function () {
        window.location.href = url;
      },
    });
  }
  $.swal_confirmation = function (text, do_action, no_action) {
    Swal.fire({
      title: "Konfirmasi?",
      text: text,
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes!"
    }).then((result) => {
      if (result.value) {
        do_action();
      } else {
        if (no_action != null) {
          no_action();
        } else {
          Swal.fire({
            title: "Konfirmasi",
            text: "Proses tidak dilanjutkan",
            type: "info",
            timer: 3000,
          });
        }
      }
    })
  }
  window.swal_success = function () {
    Swal.fire({
      title: "Success",
      text: "Data telah tersimpan",
      type: "success",
      timer: 1000,
    });
  };
  window.swal_info = function (title, text) {
    Swal.fire({
      title: title,
      type: "info",
      html: text,
      timer: 60000,
      customClass: "swal-wide",
    });
  };
  window.swal_success_title = function (title) {
    Swal.fire({
      title: "Success",
      text: "Data " + title + " telah tersimpan",
      type: "success",
      timer: 1000,
    });
  };
  window.swal_success_text = function (text) {
    Swal.fire({
      title: "Success",
      text: text,
      type: "success",
      timer: 2000,
    });
  };
  window.swal_failed_title = function (title) {
    Swal.fire({
      title: "Failed",
      text: "Data " + title + " gagal tersimpan",
      type: "error",
      timer: 1000,
    });
  };
  window.swal_error = function () {
    Swal.fire({
      title: "Failed",
      text: "Data gagal tersimpan",
      type: "error",
      timer: 5000,
    });
  };
  window.swal_error_text = function (text) {
    Swal.fire({
      title: "Error",
      text: text,
      type: "error",
      timer: 10000,
    });
  };
  window.swal_error_fill_data = function () {
    Swal.fire({
      title: "Failed",
      text: "Data lengkapi data anda",
      type: "error",
      timer: 1000,
    });
  };

  var chartCount = 0;
  var chartFinish = 0;
  var urlBase = "";
  window.initSwal = function (count, url) {
    chartCount = count;
    urlBase = url;
    chartFinish = 0;
  };
  window.addChartFinised = function () {
    chartFinish = chartFinish + 1;
  };
  window.closeLoading = function () {
    if (chartFinish >= chartCount) {
      swal.close();
      // console.log(chartCount);
      // console.log(chartFinish);
      // console.log("##");
      // chartCount = 0;
    } else {
      setTimeout(function () {
        closeLoading();
      }, 1000);
    }
  };
  window.showLoading = function () {
    Swal.fire({
      allowOutsideClick: false,
      imageUrl: urlBase + "public/assets/image/loading.gif",
      imageHeight: 100,
      imageAlt: "Please wait.....",
      showCancelButton: false,
      showConfirmButton: false,
    });
  };
});
