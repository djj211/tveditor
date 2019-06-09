/**
 * @author Daniel Jones
 */
$(document).ready(function() {
	$('a[href*="#users/delete"]').click(function() {
		var $elem = $(this);
		$('#confirmBox').html("<span class='ui-icon ui-icon-alert yellow tveditors-icon'></span>  Are You Sure You Want to Delete this user?");
		$('#confirmBox').dialog({
			modal: true,
			closeOnEscape: false,
			width: 400,
			dialogClass: "dialog-confirm",
			title: "Confirm Update",
			responsive: true,
			clickOut: true,
			buttons: {
				Yes: function() {
					var url = $elem.attr('href').replace('#', '');
					window.location.replace(url);
				},
				No: function() {
					$(this).dialog("close");
				}
			}
		});
	})
});