function loadSelectUser() {
	LtBox.show(300);
	jQuery.get("selectUser/ajax=1/", function (data) {
		jQuery("#LB_Content").html(data)
	});
}	
