$(document).ready(function(){

	$.getJSON("/category/get-in-groups",function(response){
		var app = new Vue({
			el: '#app',
			data : {
				groups: response,
				type_transfer: false
			},
			computed: {
				type_title: function(){
					if (this.type_transfer)
						return "Transfer Funds"
					else
						return "Log a Transaction"
				}
			}
		});
	});	

});