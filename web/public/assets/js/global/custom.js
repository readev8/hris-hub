function decodeEntity(inputStr) {
  var textarea = document.createElement("textarea");
  textarea.innerHTML = inputStr;
  return textarea.value;
}
function setHTML(data, id_arr) {
  id_arr.forEach((el) => {
    $(`#${el}`).html(
      data[el] != "" && data[el] != null ? decodeEntity(data[el]) : "-",
    );
  });
}

function convertDateFormat(dateString) {
  var parts = dateString.split("-");
  var day = parts[2];
  var month = parts[1];
  var year = parts[0];
  return day + "-" + month + "-" + year;
}
function format_nominal(nominal) {
  if (nominal == "" || nominal == null) return "-";
  //pisahkan titik di belakang
  var arr_nominal = nominal.split(".");
  nominal = arr_nominal[0];
  // tambah koma per 3 digit
  nominal = nominal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");

  return nominal;
}

function bulanIndo(bulan) {
  if (bulan == "" || bulan == null) return "";
  bulan = parseInt(bulan);

  switch (bulan) {
    case 1:
      return "Januari";
    case 2:
      return "Februari";
    case 3:
      return "Maret";
    case 4:
      return "April";
    case 5:
      return "Mei";
    case 6:
      return "Juni";
    case 7:
      return "Juli";
    case 8:
      return "Agustus";
    case 9:
      return "September";
    case 10:
      return "Oktober";
    case 11:
      return "November";
    case 12:
      return "Desember";
    default:
      return "";
  }
}

function disableSubmit(el, status, html = "") {
  $(el).empty();
  if (status) {
    $(el).attr("disabled", true);
    $(el).html(
      `<span style='height:15px;width:15px' class="spinner-border spinner-border-reverse align-self-center loader-sm "></span> Memproses...`,
    );
  } else {
    $(el).attr("disabled", false);
    $(el).html(html);
  }
}
$(document).ready(function () {
  // REMOVED: Old menu-btn click handler
  // Menu button is now handled by mini sidebar system in js.php
  // This prevents double event binding and conflicts

  // Keep the function for backward compatibility if needed elsewhere
  window.open_close_sidebar = function () {
    const menu_btn = document.getElementById("menu-btn");
    const menu_icon = document.getElementById("menu-icon");
    if (menu_btn.classList.contains("menu-btn-close")) {
      menu_btn.classList.add("menu-btn-open");
      menu_btn.classList.remove("menu-btn-close");

      menu_icon.classList.remove("fa-arrow-right");
      menu_icon.classList.add("fa-arrow-left");
    } else {
      menu_btn.classList.remove("menu-btn-open");
      menu_btn.classList.add("menu-btn-close");

      menu_icon.classList.add("fa-arrow-right");
      menu_icon.classList.remove("fa-arrow-left");
    }
  };
  window.add_tooltip = function (id, text) {
    // data-toggle="tooltip" data-placement="top" title="Tooltip on top"
    $(id).attr("data-toggle", "tooltip");
    $(id).attr("data-placement", "top");
    $(id).attr("title", text);
  };
  window.remove_tooltip = function (id) {
    $(id).removeAttr("data-toggle");
    $(id).removeAttr("data-placement");
    $(id).removeAttr("title");
    $(id).removeAttr("data-original-title");
  };
  window.set_tooltip = function () {
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="tooltip"]')
      .tooltip({ placement: "bottom", trigger: "manual" })
      .tooltip("show");
  };
});
(function ($) {
  $.fn.inputFilter = function (inputFilter) {
    return this.on(
      "input keydown keyup mousedown mouseup select contextmenu drop",
      function () {
        if (inputFilter(this.value)) {
          this.oldValue = this.value;
          this.oldSelectionStart = this.selectionStart;
          this.oldSelectionEnd = this.selectionEnd;
        } else if (this.hasOwnProperty("oldValue")) {
          this.value = this.oldValue;
          this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
        } else {
          this.value = "";
        }
      },
    );
  };
})(jQuery);

function loader_start(loader_element, hidden_element) {
  $(hidden_element).hide();
  $(loader_element).empty().css("height", "600px");
  $(loader_element).addClass("loader-page");
  $(loader_element).html(`
      <div class="d-flex justify-content-around w-100 loader-img" >
          <img src="${base_url}public/assets/image/loading.gif" alt="" srcset="">
      </div>
  `);
}
function loader_end(loader_element, hidden_element) {
  // console.log(new Date().toLocaleTimeString())
  $(loader_element).empty().css("height", "0px");
  $(loader_element).empty().css("padding-top", "0px");
  $(loader_element).removeClass("loader-page");
  $(hidden_element).show();
}

$(document).ready(function () {
  $(".select-2").select2();
});

//!! SEARCH MENU
$(document).ready(function () {
  $("#menuSearch").on("keyup", function () {
    //if the search input is empty, set display to none
    filterMenu();
  });

  //if user clicks outside the search input, set display to none
  $(document).click(function (e) {
    if (!$(e.target).is("#menuSearch")) {
      $("#searchResult").hide();
      $("#searchResult").removeClass("show");
    }
  });

  //if user clicks on the search input, set display to block
  $("#menuSearch").click(function () {
    $("#searchResult").show();
    $("#searchResult").addClass("show");
  });
});
function filterMenu(searchQuery) {
  // get all .submenu elements
  const items = $(".submenu");
  const search = $("#menuSearch").val().toLowerCase();
  const resultElement = $("#searchResult");
  const resultArray = [];

  //if the search input is empty, set display to none
  if (search == "") {
    resultElement.hide();
    resultElement.removeClass("show");
    return;
  } else {
    resultElement.show();
    resultElement.addClass("show");
  }

  // loop items and get ids element;'s id with .submenu
  items.each(function () {
    let modulID = $(this).attr("id");
    let modulName = $(this).data("modulname");
    let a = $(this).find(".menu-anchor");

    // loop through all the a elements
    a.each(function () {
      let menutext = modulName + " - " + $(this).text();
      let menuUrl = $(this).attr("href");

      // check if the anchor text contains the search string
      // if it does, add to resultArray
      if (menutext.toLowerCase().indexOf(search) > -1) {
        resultArray.push({ modulID, menutext, menuUrl });
      }
    });
  });
  //clear searchResult
  resultElement.empty();
  // loop through resultArray and add to searchResult
  $.each(resultArray, function (index, item) {
    if (index < 5) {
      $(resultElement)
        .append(`<a href="${item.menuUrl}" class="d-block pb-1 pt-2 px-2 small search-result-item">
        ${item.menutext} </a>`);
    }
  });
}
