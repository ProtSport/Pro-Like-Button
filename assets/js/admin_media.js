
jQuery(document).ready( function($){
    
    var updateCSS = function(){ $("#plb_your_style_css").val( editor.getSession().getValue() ); }
    $("#save-custom-css-form").submit( updateCSS );
    
});

var editor = ace.edit("customCss");
editor.setTheme("ace/theme/monokai");
editor.getSession().setMode("ace/mode/css");

$(document).ready(function($){
   

    $(".drop_down_image").click(function(){
        
    });

    // $('#view ').addAttr()
    // tabs
    $('.tab_button').click(function(){
        $(".tab_content").removeClass("active").eq($(this).index()).addClass("active");
        $('.tab_button').removeClass("active").eq($(this).index()).addClass("active");
    });
    
    // media upload
    var custom_uploader1;
    var custom_uploader2;
 
    $('#upload-button_like').on( 'click' , function(e) {
 	
        e.preventDefault();
 
        //If the uploader object has already been created, reopen the dialog
        if (custom_uploader1) {
            custom_uploader1.open();
            return;
        }
 
       //Extend the wp.media object
		custom_uploader1 = wp.media.frames.file_frame = wp.media({
			 	title: 'Choose Image',
			button: {
			 	text: 'Choose Image'
			},
			multiple: false
		});
 
        //When a file is selected, grab the URL and set it as the text field's value
        custom_uploader1.on('select', function() {
            attachment = custom_uploader1.state().get('selection').first().toJSON();
            $('#upload_image_like').val(attachment.url);
            $('.wrapp_image_admin_like').attr('src',attachment.url);
        });
 
        //Open the uploader dialog
        custom_uploader1.open();
 
    });

    $('#remove_button_like').on('click', function(e){
        e.preventDefault();
        $('#upload_image_like').val('');
        $('.wrapp_image_admin_like').attr('src','');
        $('.weather-css-setting').submit();
        return;
    });

    // 

    $('#upload-button_dislike').on( 'click' , function(e) {
    
        e.preventDefault();
 
        //If the uploader object has already been created, reopen the dialog
        if (custom_uploader2) {
            custom_uploader2.open();
            return;
        }
       //Extend the wp.media object
        custom_uploader2 = wp.media.frames.file_frame = wp.media({
                title: 'Choose Image',
            button: {
                text: 'Choose Image'
            },
            multiple: false
        });
        //When a file is selected, grab the URL and set it as the text field's value
        custom_uploader2.on('select', function() {
            attachment = custom_uploader2.state().get('selection').first().toJSON();
            $('#upload_image_dislike').val(attachment.url);
            $('.wrapp_image_admin_dislike').attr('src',attachment.url);
        });
        //Open the uploader dialog
        custom_uploader2.open();
    });

    $('#remove_button_dislike').on('click', function(e){
        e.preventDefault();
        $('#upload_image_dislike').val('');
        $('.wrapp_image_admin_dislike').attr('src','');
        $('.weather-css-setting').submit();
        return;
    });
    

    $('.js-dropdown li').on('click', function () {
        var current_image = $(this).data('number');
        var target = $(this).index();
        $('#view option').removeAttr('selected').eq(target).attr('selected', 'selected');
        var pathUrl = $('.dropdown-toggle img').attr('src').slice(0, -5);
        $('.dropdown-toggle img').attr('src', pathUrl + current_image +'.png');
    })


    $('.dropdown-toggle').click(function(e){
      $(this).next('.dropdown').toggle();
      e.preventDefault();
    });

    $(document).click(function(e) {
      var target = e.target;
      if (!$(target).is('.dropdown-toggle') && !$(target).parents().is('.dropdown-toggle')) {
        $('.dropdown').hide();
      }
    });

});