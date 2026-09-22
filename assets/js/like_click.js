// reCAPTCHA is entirely optional (see myajax.recaptchaVersion, 'none' by
// default). Resolves a token when configured, or an empty string otherwise —
// either way the vote request always goes through.
function plbGetRecaptchaToken(callback){
     if (typeof myajax === 'undefined' || !myajax.recaptchaVersion || myajax.recaptchaVersion === 'none' || typeof grecaptcha === 'undefined') {
          callback('');
          return;
     }

     if (myajax.recaptchaVersion === 'v3') {
          grecaptcha.ready(function(){
               grecaptcha.execute(myajax.recaptchaSiteKey, {action: 'plb_vote'}).then(function(token){
                    callback(token || '');
               }, function(){
                    callback('');
               });
          });
          return;
     }

     if (myajax.recaptchaVersion === 'v2') {
          var response = grecaptcha.getResponse();
          if (!response) {
               alert('Please confirm you are not a robot.');
               return;
          }
          callback(response);
          return;
     }

     callback('');
}

jQuery(document).ready(function(){
          // when the user clicks on like
          jQuery('.like').on('click', function(){

               var postid = jQuery(this).data('id');
               var type = jQuery(this).data('type') || 'post';
                   $post = jQuery(this);

               plbGetRecaptchaToken(function(token){
                    var data = {
                         action: 'id',
                         nonce: myajax.nonce,
                         postid: postid,
                         type: type,
                         liked: 1,
                         recaptcha_token: token,
                    };
                    jQuery.post( myajax.url, data, function(response) {
                         $post.parent().find('.likes_count_like').text(response);
                    });
               });
          });


          // when the user clicks on unlike
          jQuery('.unlike').on('click', function(){

               var postid = jQuery(this).data('id');
               var type = jQuery(this).data('type') || 'post';
                   $post = jQuery(this);

               plbGetRecaptchaToken(function(token){
                    var data = {
                         action: 'id',
                         nonce: myajax.nonce,
                         postid: postid,
                         type: type,
                         unliked: 1,
                         recaptcha_token: token,
                    };
                    jQuery.post( myajax.url, data, function(response) {
                         $post.parent().find('.likes_count_dislike').text(response);
                         $post.parent().find('.unlike').addClass('active');
                    });
               });
          });



          var files;
          jQuery('input[type=file]').on('change', function(){
               files = this.files;
               console.log(files);
               alert('1');
          });
     });
