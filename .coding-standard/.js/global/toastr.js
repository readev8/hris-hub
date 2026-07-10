toastr.options = {
	closeButton: true,
	debug: false,
	newestOnTop: false,
	progressBar: true,
	positionClass: "toast-bottom-right",
	preventDuplicates: false,
	onclick: null,
	showDuration: "300",
	hideDuration: "1000",
	timeOut: "3000",
	extendedTimeOut: "1000",
	showEasing: "swing",
	hideEasing: "linear",
	showMethod: "fadeIn",
	hideMethod: "fadeOut",
};
$(document).ready(function () {
	window.toastr_success = function (title, text) {
		Command: toastr["success"](text, title);
	};
	window.toastr_info = function (title, text) {
		Command: toastr["info"](text, title);
	};
	window.toastr_warning = function (title, text) {
		Command: toastr["warning"](text, title);
	};
	window.toastr_error = function (title, text) {
		Command: toastr["error"](text, title);
	};
});
