$(document).ready(function () {
  $.to_detail = function (id, url, filter) {
    var entityGrid = $(id).data("kendoGrid");
    var selectedItem = entityGrid.dataItem(entityGrid.select());
    if (selectedItem === null) {
      swal_error_text("Silahkan pilih data terlebih dahulu");
    } else {
      if (filter != null) {
        var currentURL = window.location.href;
        $.post(
          `${site_url}helper/sf`,
          { filter: JSON.stringify(filter), url: currentURL },
          function (data) {
            window.open(
              `${url}${selectedItem["token"]}&f=${data.filter}`,
              "_self"
            );
          }
        );
      } else {
        window.open(`${url}${selectedItem["token"]}`, "_self");
      }
    }
  };
});

$(document).ready(function () {
  window.kendo_table = function (id, data, fields, columns) {
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
      // filterable: true,
      selectable: "single",
      dataBound: onDataBound,
      pageable: {
        input: true,
        numeric: false,
      },
      filterable: {
        mode: "row",
      },
      scrollable: true,

      columns: columns,
    });
  };
  window.kendo_table_2 = function (id, data, fields, columns) {
    var height = $(window).height();
    var tableHeight = (height * 2) / 3;
    var grid = $(id)
      .kendoGrid({
        dataSource: {
          data: data,
          schema: {
            model: {
              fields: fields,
            },
          },
          pageSize: 50,
        },
        height: tableHeight,
        sortable: true,
        reorderable: true,
        groupable: true,
        resizable: true,
        columnMenu: true,
        pageable: true,
        groupable: false, // Please set here true/false and check it
        // filterable: true,
        dataBound: onDataBound,
        pageable: {
          input: true,
          numeric: false,
        },
        filterable: {
          mode: "row",
        },
        scrollable: true,

        columns: columns,
      })
      .data("kendoGrid");
  };
  window.kendo_table_3 = function (id, data, fields, columns) {
    var height = $(window).height();
    var tableHeight = (height * 1) / 3;
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
      // filterable: true,
      dataBound: onDataBound,
      pageable: {
        input: true,
        numeric: false,
      },
      filterable: {
        mode: "row",
      },
      scrollable: true,

      columns: columns,
    });
  };
  window.kendo_table_4 = function (id, data, fields, columns) {
    var height = $(window).height();
    var tableHeight = (height * 6) / 7;
    $(id).kendoGrid({
      dataSource: {
        data: data,
        schema: {
          model: {
            fields: fields,
          },
        },
        pageSize: 10,
      },
      height: tableHeight,
      sortable: true,
      reorderable: true,
      groupable: true,
      resizable: true,
      columnMenu: true,
      pageable: true,
      groupable: false, // Please set here true/false and check it
      // filterable: true,
      dataBound: onDataBound,
      pageable: {
        input: true,
        numeric: false,
      },
      filterable: {
        mode: "row",
      },
      scrollable: true,

      columns: columns,
    });
  };

  window.kendo_table_modal = function (id, data, fields, columns) {
    var height = $(window).height();
    var tableHeight = height - 200;
    $(id).kendoGrid({
      dataSource: {
        data: data,
        schema: {
          model: {
            fields: fields,
          },
        },
        pageSize: 5,
      },
      // height: tableHeight,
      scrollable: true,
      pageable: true,
      resizable: true,
      columnMenu: true,
      reorderable: true,
      filterable: {
        mode: "row",
        cell: {
          suggestionOperator: "contains",
        },
      },
      // sortable: true,
      // groupable: true,
      groupable: false, // Please set here true/false and check it
      // filterable: true,
      // dataBound: onDataBound,
      // pageable: {
      // 	input: true,
      // 	numeric: false,
      // },

      columns: columns,
    });
  };

  window.kendo_table_dragable = function (id, data, fields, columns) {
    var height = $(window).height();

    var tableHeight = (height * 2) / 3;
    var dataSource = new kendo.data.DataSource({
      data: data,
      schema: {
        model: {
          id: "id",
          fields: {
            id: { type: "number" },
            pertanyaan: { type: "string" },
            position: { type: "number" },
          },
        },
      },
    });

    var grid = $(id)
      .kendoGrid({
        dataSource: dataSource,
        // height: tableHeight,
        sortable: true,
        reorderable: true,
        groupable: true,
        resizable: true,
        columnMenu: true,
        pageable: true,
        groupable: false, // Please set here true/false and check it
        // filterable: true,
        dataBound: onDataBound,
        pageable: {
          input: true,
          numeric: false,
        },
        filterable: {
          mode: "row",
        },
        scrollable: true,
        columns: columns,
      })
      .data("kendoGrid");
  };
  window.kendo_table_databound = function (id, data, fields, columns) {
    var height = $(window).height();
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
      // filterable: true,
      dataBound: onDataBadge,
      pageable: {
        input: true,
        numeric: false,
      },
      filterable: {
        mode: "row",
      },
      scrollable: true,

      columns: columns,
    });
  };
  function onDataBadge(e) {
    var grid = this;
    grid.table.find("tr").each(function () {
      var dataItem = grid.dataItem(this);
      var themeColor = dataItem.Discontinued ? "success" : "error";
      // var text = dataItem.Discontinued ? 'available' : 'not available';

      // $(this).find(".badgeTemplate").kendoBadge({
      //     themeColor: 'success',
      //     text: dataItem.status,
      // });

      // $(this).find(".rating").kendoRating({
      //     min: 1,
      //     max: 5,
      //     label: false,
      //     selection: "continuous"
      // });

      // $(this).find(".sparkline-chart").kendoSparkline({
      //     legend: {
      //         visible: false
      //     },
      //     data: [dataItem.TargetSales],
      //     type: "bar",
      //     chartArea: {
      //         margin: 0,
      //         width: 180,
      //         background: "transparent"
      //     },
      //     seriesDefaults: {
      //         labels: {
      //             visible: true,
      //             format: '{0}%',
      //             background: 'none'
      //         }
      //     },
      //     categoryAxis: {
      //         majorGridLines: {
      //             visible: false
      //         },
      //         majorTicks: {
      //             visible: false
      //         }
      //     },
      //     valueAxis: {
      //         type: "numeric",
      //         min: 0,
      //         max: 130,
      //         visible: false,
      //         labels: {
      //             visible: false
      //         },
      //         minorTicks: { visible: false },
      //         majorGridLines: { visible: false }
      //     },
      //     tooltip: {
      //         visible: false
      //     }
      // });

      kendo.bind($(this), dataItem);
    });
  }
  function onDataBound(e) {
    var grid = this;
    grid.table.find("tr").each(function () {
      var dataItem = grid.dataItem(this);
      // var themeColor = dataItem.Discontinued ? 'success' : 'error';
      // var text = dataItem.Discontinued ? 'available' : 'not available';

      // $(this).find(".badgeTemplate").kendoBadge({
      //     themeColor: 'success',
      //     text: dataItem.status,
      // });

      // $(this).find(".rating").kendoRating({
      //     min: 1,
      //     max: 5,
      //     label: false,
      //     selection: "continuous"
      // });

      // $(this).find(".sparkline-chart").kendoSparkline({
      //     legend: {
      //         visible: false
      //     },
      //     data: [dataItem.TargetSales],
      //     type: "bar",
      //     chartArea: {
      //         margin: 0,
      //         width: 180,
      //         background: "transparent"
      //     },
      //     seriesDefaults: {
      //         labels: {
      //             visible: true,
      //             format: '{0}%',
      //             background: 'none'
      //         }
      //     },
      //     categoryAxis: {
      //         majorGridLines: {
      //             visible: false
      //         },
      //         majorTicks: {
      //             visible: false
      //         }
      //     },
      //     valueAxis: {
      //         type: "numeric",
      //         min: 0,
      //         max: 130,
      //         visible: false,
      //         labels: {
      //             visible: false
      //         },
      //         minorTicks: { visible: false },
      //         majorGridLines: { visible: false }
      //     },
      //     tooltip: {
      //         visible: false
      //     }
      // });

      kendo.bind($(this), dataItem);
    });
  }
  /*
  /**
   * Fungsi ini digunakan untuk mengambil row value dari baris yang "terpilih", jika ada button di baris tersebut, fungsi ini dapat digunakan
   * @param {string} gridId - id dari kendo grid
   * @param {object} row - baris yang terpilih
   * @param {string} field - nama field yang ingin di ambil nilainya
   * @returns {object} nilai dari field yang di ambil
   */
  window.getSelectedRowValue = function (gridId, row, field) {
    var row = $(row).closest("tr");
    var grid = $(gridId).data("kendoGrid");
    var dataItem = grid.dataItem(row);
    return dataItem[field];
  };
  $.getKendoRowValue = function (gridId, row) {
    var row = $(row).closest("tr");
    var grid = $(gridId).data("kendoGrid");
    var dataItem = grid.dataItem(row);
    return dataItem;
  };
  $.getSelectedDataItem = function (gridId, row) {
    var grid = $(gridId).data("kendoGrid");

    // Dapatkan elemen baris yang dipilih
    var selectedRow = grid.select();

    // Periksa apakah ada baris yang dipilih
    if (selectedRow.length > 0) {
      // Dapatkan data item (objek data) dari baris yang dipilih
      var dataItem = grid.dataItem(selectedRow);
      // Akses nilai dari data item
      return dataItem;
    } else {
      return null;
    }
  };
});

/**
 * ═══════════════════════════════════════════════════════════════════════════════
 * HR-Plus LIST View Standardized Functions
 * ═══════════════════════════════════════════════════════════════════════════════
 *
 * Standardized functions for LIST view page initialization
 * following HR-Plus coding standards.
 *
 * @since 2026-01-04
 * @version 1.0.0
 */

/**
 * Get standard Kendo Grid height based on window height
 *
 * Calculates appropriate grid height using CONSTANTS.GRID.DEFAULT_HEIGHT_OFFSET
 *
 * @param {number} customOffset - Custom offset value if needed (optional)
 * @returns {number} Grid height in pixels
 *
 * @example
 * // Standard usage (uses CONSTANTS.GRID.DEFAULT_HEIGHT_OFFSET)
 * var height = $.getKendoGridHeight();
 *
 * @example
 * // With custom offset
 * var height = $.getKendoGridHeight(100);
 *
 * @requires CONSTANTS.GRID.DEFAULT_HEIGHT_OFFSET from gc.js
 */
$.getKendoGridHeight = function (customOffset) {
  var offset =
    customOffset !== undefined
      ? customOffset
      : CONSTANTS.GRID.DEFAULT_HEIGHT_OFFSET;
  return $(window).height() - offset;
};

/**
 * Validate if item is selected in Kendo Grid
 *
 * Standard validation function for grid selection
 *
 * @param {Object|null} selectedItem - Item from getSelectedGridItem()
 * @returns {boolean} True if item is selected, false otherwise
 *
 * @example
 * var selectedItem = getSelectedGridItem();
 * if (!$.isKendoGridItemSelected(selectedItem)) {
 *   swal_error_text(CONSTANTS.MESSAGES.INFO.NO_SELECTION);
 *   return;
 * }
 */
$.isKendoGridItemSelected = function (selectedItem) {
  return selectedItem !== null && selectedItem !== undefined;
};

/**
 * Get selected item from Kendo Grid with error handling
 *
 * Standard function to get selected grid item safely
 *
 * @param {string|jQuery} gridSelector - Grid selector or jQuery object
 * @returns {Object|null} Selected data item or null if error
 *
 * @example
 * var selectedItem = $.getKendoGridSelectedItem("#grid");
 * if (!$.isKendoGridItemSelected(selectedItem)) {
 *   // Handle no selection
 * }
 */
$.getKendoGridSelectedItem = function (gridSelector) {
  try {
    var $grid =
      typeof gridSelector === "string" ? $(gridSelector) : gridSelector;
    var entityGrid = $grid.data("kendoGrid");

    if (!entityGrid) {
      throw new Error("Kendo Grid not found");
    }

    var selectedItem = entityGrid.dataItem(entityGrid.select());
    return selectedItem || null;
  } catch (error) {
    console.error("Error getting selected grid item:", error);
    return null;
  }
};

/**
 * Check if item is in locked status
 *
 * Standard lock status check function
 *
 * @param {Object} item - Grid data item
 * @returns {boolean} True if locked, false otherwise
 *
 * @example
 * if ($.isGridItemLocked(selectedItem)) {
 *   swal_error_text(CONSTANTS.MESSAGES.SPECIFIC.JOBCODE_LOCKED);
 *   return;
 * }
 */
$.isGridItemLocked = function (item) {
  if (!item) return false;
  var status = item.lockedstatus;
  return String(status) === CONSTANTS.LOCK_STATUS.LOCKED;
};

/**
 * Check if item is in unlocked status
 *
 * Standard unlock status check function
 *
 * @param {Object} item - Grid data item
 * @returns {boolean} True if unlocked, false otherwise
 *
 * @example
 * if ($.isGridItemUnlocked(selectedItem)) {
 *   swal_error_text(CONSTANTS.MESSAGES.SPECIFIC.JOBDESC_NOT_LOCKED);
 *   return;
 * }
 */
$.isGridItemUnlocked = function (item) {
  if (!item) return false;
  var status = item.lockedstatus;
  return (
    status == null ||
    status === undefined ||
    String(status) === CONSTANTS.LOCK_STATUS.UNLOCKED
  );
};
