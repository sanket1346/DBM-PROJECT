define(['jquery'],
 function($) {
    'use strict';
    return  {
		showDefult: function () {
			 var value = $("#cms_type").val();
			 $(".field-type1_content").hide();
		 	 $(".field-type2_content1").hide();
		 	 $(".field-type2_content2").hide();
		 	 $(".field-type3_content1").hide();
		 	 $(".field-type3_content2").hide();
		 	 $(".field-type3_content3").hide();
		 	 $(".field-type4_content1").hide();
		 	 $(".field-type4_content2").hide();
		 	 $(".field-type4_content3").hide();
		 	 $(".field-type4_content4").hide();
		 	 $(".field-type1_previewimage").hide();
		 	 if(value == 1){
		 	 	$(".field-type1_content").show();
		 	 	$(".field-type1_previewimage").show();
		 	 	jQuery('.field-type1_previewimage img').addClass('hidden');
		 	 	jQuery('.field-type1_previewimage img[data-value="'+value+'"]').removeClass('hidden');

		 	 }if(value == 2){
		 	 	$(".field-type2_content1").show();
		 	 	$(".field-type2_content2").show();
		 	 	$(".field-type1_previewimage").show();
		 	 	jQuery('.field-type1_previewimage img').addClass('hidden');
		 	 	jQuery('.field-type1_previewimage img[data-value="'+value+'"]').removeClass('hidden');
		 	 }
		 	 if(value == 3){
		 	 	$(".field-type3_content1").show();
			 	$(".field-type3_content2").show();
			 	$(".field-type3_content3").show();
			 	//$(".field-type1_previewimage").show();
		 	 	//jQuery('.field-type1_previewimage img').addClass('hidden');
		 	 	//jQuery('.field-type1_previewimage img[data-value="'+value+'"]').removeClass('hidden');
		 	 }
		 	 if(value == 4){
		 	 	$(".field-type4_content1").show();
			 	 $(".field-type4_content2").show();
			 	 $(".field-type4_content3").show();
			 	 $(".field-type4_content4").show();
			 	 $(".field-type1_previewimage").show();
		 	 	jQuery('.field-type1_previewimage img').addClass('hidden');
		 	 	jQuery('.field-type1_previewimage img[data-value="'+value+'"]').removeClass('hidden');
		 	 }
		 	 if(value == 5){
		 	 	$(".field-type1_previewimage").show();
		 	 	jQuery('.field-type1_previewimage img').addClass('hidden');
		 	 	jQuery('.field-type1_previewimage img[data-value="'+value+'"]').removeClass('hidden');
		 	 } 
		 	 if(value == 6){
		 	 	$(".field-type1_previewimage").show();
		 	 	jQuery('.field-type1_previewimage img').addClass('hidden');
		 	 	jQuery('.field-type1_previewimage img[data-value="'+value+'"]').removeClass('hidden');
		 	 } 	
			//var self = this,mainFieldVal = $("#cms_type").val()
	    }

    }

});