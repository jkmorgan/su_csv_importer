jQuery(function($){ 
	$(document).ready(function(){
		//alert('asd');
	});
	$('.btn').click(function(){
		for (var i = 0; i < document.getElementById("upload_input").files.length; i++){
			var raw_file = document.getElementById("upload_input").files[i];
			readFile(raw_file, function(e){
				file = e.target.result;
				console.log(file.length);
				console.log(window);
				$.ajax({
					url:ajaxurl,
					type:'POST',
					data:{
						action:'csv',
						file:file
					},
					complete:function(e, s){
						if (s == 'success'){
							console.log(e.responseText);
						}
					}
				});//ajaxend
		
			});	
		}
		
	});//functend

});//jq end
	
function readFile(file, onLoadCallBack){
	var reader = new FileReader();
	reader.onload = onLoadCallBack;
	reader.readAsText(file);
}