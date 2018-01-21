// Move table of contents to sidebar
// Also see css/toc.css
if ($("#toc").length) {
	$("#toc-sidebar .container-fluid").append($("#toc"));
} else {
	$("#toc-sidebar").removeClass("d-md-block").addClass("d-xl-block");
}
