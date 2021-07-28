require([
    "jquery"
], function ($) {
    $(document).ready(function () {
        //var newbreadcrumb = $('.replacebreadcrumbs').html();
        $('.breadcrumbs').hide();
		$('.full-breadcrumbs .breadcrumbs').show();
        /*$(".replacebreadcrumbs").prependTo("#maincontent");
        $('.replacebreadcrumbs').fadeIn();*/
    });
});