
$(function(){
	//$("input[name='address_options']").click(set_address);
	$('.address_item').click(function(){
		$(this).find("input[name='address_options']").attr('checked', true);
		$('.address_item').removeClass('selected_address');
		$(this).addClass('selected_address');
		set_address();
	});
	//init
	set_address();
});
function set_address(){
	var addr_id = $("input[name='address_options']:checked").val();
	if(addr_id == 0)
	{
		$('#consignee').val("");
		$('#region_name').val("");
		$('#region_id').val("");
		//$('#select').show();
		$("#edit_region_button").hide();
		$('#region_name_span').hide();

		$('#address').val("");
		$('#zipcode').val("");
		$('#phone_tel').val("");
		$('#phone_mob').val("");

		$('#address_form').show();
	}
	else
	{
		$('#address_form').hide();
		fill_address_form(addr_id);
	}
}
function fill_address_form(addr_id){
	if(typeof address == 'undefined'){
		return;
	}
	var addr_data = addresses[addr_id];
	for(k in addr_data){
		switch(k){
			case 'consignee':
			case 'address':
			case 'zipcode':
			case 'email':
			case 'phone_tel':
			case 'phone_mob':
				var s = $("input[name='" + k + "']");
				s.val(addr_data[k]);
			break;
			case 'region_id':							
				$("#region input[name='" + k + "']").val(addr_data[k]);
			break;
			case 'region_name':
				$("#region  input[name='" + k + "']").val(addr_data[k]);
				$('#region #region select').hide();
				$('#region #region_name_span').text(addr_data[k]).show();
				$("#region #edit_region_button").show();
			break;
		}
	}
}