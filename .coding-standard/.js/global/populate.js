const select2Elements = $(".select2");
select2Elements.select2();
$.populateSelect2 = function (id, data, placeholder = null, field_text = null) {
  $(id).empty();
  $(id).append(`<option value="0">${placeholder}</option>`);
  // if (placeholder != null)
  //     $(id).attr('data-placeholder', placeholder).select2({ placeholder: placeholder });
  data.forEach((val_data) => {
    if (field_text == null)
      $(id).append(`<option value="${val_data.id}">${val_data.nama}</option>`);
    else
      $(id).append(
        `<option value="${val_data.id}">${val_data[field_text]}</option>`,
      );
  });
  if (data.length == 1) $(id).val(data[0].id).trigger("change");
};
$.populateSelect2MultiSelect = function (
  id,
  data,
  placeholder = null,
  preserveSelection = false,
) {
  console.log(data);
  const idSelector = id.startsWith("#") ? id : "#" + id;
  const $element = $(idSelector);

  // Simpan nilai yang sudah dipilih jika preserveSelection = true
  let selectedValues = [];
  if (preserveSelection && $element.length) {
    selectedValues = $element.val() || [];
  }

  // Destroy select2 instance if it exists
  if ($element.hasClass("select2-hidden-accessible")) {
    $element.select2("destroy");
  }

  // Clear existing options
  $element.empty().val(null);

  // Initialize select2 with new data
  $element.select2({
    data: data,
    placeholder: placeholder || "Pilih opsi",
    allowClear: true,
    closeOnSelect: false,
    width: "100%", // Ensure full width
    // Fungsi untuk merender tampilan item di dropdown
    templateResult: function (data) {
      // Jika tidak ada data atau id, jangan tampilkan apa-apa (cocok untuk placeholder)
      if (!data.id) {
        return data.text;
      }

      // Cek apakah opsi ini sudah terpilih
      var isSelected =
        data.selected || selectedValues.includes(data.id.toString());

      // Buat elemen checkbox
      var $checkbox = $(
        '<input class="checkbox-input" type="checkbox" ' +
          (isSelected ? "checked" : "") +
          "/>",
      );
      // Buat elemen span untuk teks
      var $text = $('<span class="checkbox-text"> ' + data.text + "</span>");
      // Gabungkan checkbox dan teks dalam satu kontainer
      var $container = $("<span></span>").append($checkbox).append($text);

      return $container;
    },
    // Fungsi untuk merender tampilan item yang sudah dipilih
    templateSelection: function (data) {
      // Tampilkan teks biasa untuk item yang sudah dipilih
      return data.text;
    },
  });

  // Restore previous selection if preserveSelection = true
  if (preserveSelection && selectedValues.length > 0) {
    // Trigger change setelah menetapkan nilai
    setTimeout(function () {
      $element.val(selectedValues).trigger("change");
    }, 100);
  }
};

var select2_checkbox_clicked = false;
$(document).on("click", ".select2-results__option", function (e) {
  console.log("row clicked");
  if (!select2_checkbox_clicked) {
    var checkbox = $(this).find("input");
    if (checkbox.prop("checked")) {
      checkbox.prop("checked", false);
    } else {
      checkbox.prop("checked", true);
    }
  }
  select2_checkbox_clicked = false;
});
$(document).on(
  "click",
  ".select2-results__option .checkbox-input",
  function (e) {
    select2_checkbox_clicked = true;
    // e.preventDefault();
    console.log("checkbox clicked");
  },
);
// Fungsi tambahan untuk update data tanpa destroy
$.updateSelect2MultiSelectData = function (id, newData, placeholder = null) {
  const $element = $("#" + id);

  if (!$element.hasClass("select2-hidden-accessible")) {
    // If select2 not initialized, initialize it first
    $.populateSelect2MultiSelect(id, newData, placeholder);
    return;
  }

  // Get current selected values
  const currentValues = $element.val() || [];

  // Update select2 data
  $element.empty().select2({
    data: newData,
    placeholder: placeholder || "Pilih opsi",
    allowClear: true,
    closeOnSelect: false,
    width: "100%",
    templateResult: function (data) {
      if (!data.id) {
        return data.text;
      }
      var isSelected =
        data.selected || currentValues.includes(data.id.toString());
      var $checkbox = $(
        '<input  type="checkbox" ' + (isSelected ? "checked" : "") + "/>",
      );
      var $text = $("<span> " + data.text + "</span>");
      var $container = $("<span></span>").append($checkbox).append($text);
      return $container;
    },
    templateSelection: function (data) {
      return data.text;
    },
  });

  // Restore selection
  $element.val(currentValues).trigger("change");
};

// Fungsi untuk clear selection
$.clearSelect2MultiSelect = function (id) {
  $("#" + id)
    .val(null)
    .trigger("change");
};

// Fungsi untuk get selected values
$.getSelect2MultiSelectValues = function (id) {
  return $("#" + id).val() || [];
};
$.populateRadioButton = function (panelid, data) {
  data.forEach((val_data) => {
    var pnlid = panelid.replace("#", "");
    $(`${panelid}`).append(`
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" id="checkbox-${pnlid}-${val_data.id}" name="${pnlid}" value="${val_data.id}" data-input_type="radio" >
            <label class="form-check-label" for="exampleRadios1">
                ${val_data.nama}
            </label>
        </div>
        `);
  });
};
$.populateError = function (data) {
  // data error menggunakan standard error dari code igniter 4
  console.log(data);
  $(".error-notification").remove();
  var total = 0;
  for (const key in data) {
    if (data.hasOwnProperty(key)) {
      const element = data[key];
      var template = `<span class="text-danger error-notification" id="error-${key}">${element}</span>`;
      if ($(`#${key}`).hasClass("select2-hidden-accessible")) {
        $(`#${key}`).next().after(template);
        total++;
      } else if ($(`#${key}`).hasClass("radio-panel-l2")) {
        var template = `<div class='col-sm-2'></div><div class="col-sm-10"><span class="text-danger error-notification" id="error-${key}">${element}</span></div>`;
        $(`#${key}`).after(template);
        total++;
      } else if ($(`#${key}`).hasClass("radio-panel-l3")) {
        var template = `<div class='col-sm-3'></div><div class="col-sm-9"><span class="text-danger error-notification" id="error-${key}">${element}</span></div>`;
        $(`#${key}`).after(template);
        total++;
      } else if ($(`#${key}`).hasClass("radio-panel-l4")) {
        var template = `<div class='col-sm-4'></div><div class="col-sm-8"><span class="text-danger error-notification" id="error-${key}">${element}</span></div>`;
        $(`#${key}`).after(template);
        total++;
      } else if ($(`#${key}`).hasClass("group-panel-l2")) {
        var template = `<div class='col-sm-2'></div><div class="col-sm-10"><span class="text-danger error-notification" id="error-${key}">${element}</span></div>`;
        $(`#${key}`).after(template);
        total++;
      } else if ($(`#${key}`).hasClass("group-panel-l3")) {
        var template = `<div class='col-sm-3'></div><div class="col-sm-9"><span class="text-danger error-notification" id="error-${key}">${element}</span></div>`;
        $(`#${key}`).after(template);
        total++;
      } else if ($(`#${key}`).hasClass("group-panel-l4")) {
        var template = `<div class='col-sm-4'></div><div class="col-sm-8"><span class="text-danger error-notification" id="error-${key}">${element}</span></div>`;
        $(`#${key}`).after(template);
        total++;
      } else {
        $(`#${key}`).after(template);
        total++;
      }
    }
  }

  if (total == 1 && data.hasOwnProperty("other-rules")) {
    // const element = data['other-rules'];
    $.swal_error(data["other-rules"]);
  }
};
$.clearError = function () {
  $(".error-notification").remove();
};
$.populateArrayToDatatable = function (id, data, columns, options = {}) {
  /**
   * Contoh input untuk fungsi ini:
   *
   * const id = 'table-data';
   * const data = [
   *     { id: 1, nama: 'Andi', email: 'andi@example.com', tanggal_lahir: '1990-01-01' },
   *     { id: 2, nama: 'Budi', email: 'budi@example.com', tanggal_lahir: '1991-01-01' },
   *     { id: 3, nama: 'Caca', email: 'caca@example.com', tanggal_lahir: '1992-01-01' },
   * ];
   * const columns = [
   *     { title: 'ID', data: 'id' },
   *     { title: 'Nama', data: 'nama' },
   *     { title: 'Email', data: 'email' },
   *     { title: 'Tanggal Lahir', data: 'tanggal_lahir' },
   * ];
   * $.populateArrayToDatatable(id, data, columns);
   */
  const tableElement = $("#" + id);

  // Hancurkan instance DataTables yang ada jika sudah diinisialisasi
  if ($.fn.DataTable.isDataTable(tableElement)) {
    tableElement.DataTable().destroy();
    tableElement.empty(); // Kosongkan elemen tabel untuk menghindari duplikasi header/footer
  }

  // Inisialisasi DataTables dengan data dan kolom yang diberikan
  tableElement.DataTable({
    data: data,
    columns: columns,
    ...options,
  });
};
// $.populateArrayToDatatableSimple = function (id, data) {
//     const tableElement = $('#' + id);

//     // Hancurkan instance DataTables yang ada jika sudah diinisialisasi
//     if ($.fn.DataTable.isDataTable(tableElement)) {
//         tableElement.DataTable().destroy();
//         tableElement.empty(); // Kosongkan elemen tabel untuk menghindari duplikasi header/footer
//     }

// };

// FUNGSI INI DIGUNAKAN UNTUK POPULATE DATA KE LIST KENDO (HALAMAN BESAR)
$.populateKendoTable = function (id, data, fields, columns) {
  var height = $(window).height() - 60;
  var tableHeight = height;
  $(id).kendoGrid({
    dataSource: {
      data: data,
      schema: {
        model: {
          fields: fields,
        },
      },
      pageSize: 20,
    },
    height: tableHeight,
    sortable: true,
    reorderable: true,
    groupable: true,
    resizable: true,
    columnMenu: true,
    pageable: true,
    groupable: false, // Please set here true/false and check it
    selectable: "single",

    pageable: {
      input: true,
      numeric: false,
    },
    filterable: {
      mode: "row",
    },
    scrollable: true,
    columns: columns,
    // toolbar: ["excel"], // <-- Tambahkan baris ini

    // excel: {
    //     fileName: "Export.xlsx",
    //     filterable: true
    // }
    // dataBound: function () {},
    // dataBound: onDataBound,
  });
};
// FUNGSI INI DIGUNAKAN UNTUK POPULATE DATA KE LIST KENDO (DALAM CARD)

$.populateKendoTableSimple = function (id, data, fields, columns) {
  // var tableHeight = height;
  $(id).kendoGrid({
    dataSource: data,
    pageable: true,
    selectable: "single",
    columns: columns,
  });
};
// FUNGSI INI DIGUNAKAN UNTUK POPULATE DATA KE LIST KENDO (DALAM MODAL)

$.populateKendoTableSimplePaging = function (id, data, fields, columns) {
  // var tableHeight = height;
  $(id).kendoGrid({
    dataSource: {
      data: data,

      pageSize: 10,
    },
    pageable: true,
    selectable: "single",
    columns: columns,
    filterable: {
      mode: "row",
    },
  });
};
$.populateKendoTableMultiSelect = function (id, data, fields, columns) {
  var height = $(window).height() - 60;
  var tableHeight = height;
  $(id).kendoGrid({
    // Perubahan utama di sini
    dataSource: {
      // Langsung masukkan array data
      data: data,
      pageSize: 20, // Diubah menjadi 5 agar paging lebih terlihat
      serverPaging: false,
      serverSorting: false,
      serverFiltering: false,
    },
    height: tableHeight,
    // editable: true,
    filterable: true,
    pageable: true,
    sortable: true,
    selectable: "single",
    columns: generateColumnsProperty(columns),
  });
};
$.populateKendoFilter = function (id, filter) {
  if (filter == null || filter == "") return;

  if (filter.filter == null || filter.filter.logic != null) {
    $(`#${id}`).data("kendoGrid").dataSource.filter(filter);
  } else {
    $(`#${id}`).data("kendoGrid").dataSource.filter(filter.filters);
  }
  if ($(`#NV_CLEAR_FILTER`).length > 0) {
    $(`#NV_CLEAR_FILTER`).on("click", function (e) {
      Swal.fire({
        title: "Anda yakin ingin menghapus filter?",
        text: "Semua filter akan dihapus",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Ya, hapus!",
      }).then((result) => {
        if (result.value) {
          $(`#${id}`).data("kendoGrid").dataSource.filter({});
          $("#NV_CLEAR_FILTER").hide();
        }
      });
    });
  }
  // console.log(res.filter.filter);
};
function generateColumnsProperty(columns) {
  $.each(columns, function (index, val) {
    val["filterable"] = {
      multi: true,
      search: true,
    };
  });
  return columns;
}
$.populateDataToForm = function (data) {
  // FILL TEXT/TEXTAREA/RADIO BUTTON
  $.each(data, function (index, val) {
    $(`#${index}`).val(val).trigger("change"); // TEXTBOX AND TEXTAREA USING ID

    if (Number.isInteger(val)) {
      const element = $('[name="' + index + '"][value="' + val + '"]'); // RADIO BUTTON USING NAME
      if (element.length > 0) {
        $(element).prop("checked", true);
      }
    }
  });
};
$.populateDisabledDataForm = function (data) {
  // FILL TEXT/TEXTAREA/RADIO BUTTON
  $.each(data, function (index, val) {
    $(`#${index}`).prop("disabled", true); // TEXTBOX AND TEXTAREA USING ID
    $(`#${index}`).val(val).trigger("change"); // TEXTBOX AND TEXTAREA USING ID

    // console.log(`[name="${index}"][value="${val}"]`);
    if (Number.isInteger(val)) {
      const element = $('[name="' + index + '"][value="' + val + '"]'); // RADIO BUTTON USING NAME
      if (element.length > 0) {
        $(element).prop("checked", true);
      }
    }
  });
};
$.populateDataToForControl = function (data) {
  // FILL TEXT/TEXTAREA/RADIO BUTTON
  $.each(data, function (index, val) {
    $(`#${index}`).val(val).trigger("change"); // TEXTBOX AND TEXTAREA USING ID

    // console.log(`[name="${index}"][value="${val}"]`);
    if (Number.isInteger(val)) {
      const element = $('[name="' + index + '"][value="' + val + '"]'); // RADIO BUTTON USING NAME
      if (element.length > 0) {
        $(element).prop("checked", true);
      }
    }
  });
};
// $.polulateDataToTextBox = function (data,alias){
//     // FILL TEXT/TEXTAREA/RADIO BUTTON
//     $.each(data, function (index, val) {
//         $(`#input${alias}`).val(val)// TEXTBOX AND TEXTAREA USING ID
//     })
// }
