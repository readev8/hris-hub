$(document).ready(function () {
	window.initializeCombobox = function (id) {
		$(id).multiselect({
			buttonWidth: "100%",
			includeSelectAllOption: true,
			maxHeight: 200,
			enableFiltering: true,
		});
		$(id).multiselect("selectAll", false);
		$(id).multiselect("updateButtonText");
	};
	window.initializeComboboxSingleSelected = function (id, data) {
		$("#".id).select2({
			data: data,
		});
	};
});
