jQuery(document).ready(function(){
          // when the user clicks on like
          jQuery('.like').on('click', function(){

               var postid = jQuery(this).data('id');
               var type = jQuery(this).data('type') || 'post';
                   $post = jQuery(this);
               var data = {
                    action: 'id',
                    nonce: myajax.nonce,
                    postid: postid,
                    type: type,
                    liked: 1,
               };
               // $(this).addClass('user-active_like');
               jQuery.post( myajax.url, data, function(response) {
                    $post.parent().find('.likes_count_like').text(response);

               });
          });

      
          // when the user clicks on unlike
          jQuery('.unlike').on('click', function(){

               var postid = jQuery(this).data('id');
               var type = jQuery(this).data('type') || 'post';
                   $post = jQuery(this);
               var data = {
                    action: 'id',
                    nonce: myajax.nonce,
                    postid: postid,
                    type: type,
                    unliked: 1,
               };
               // $(this).addClass('user-active_dislike');
               jQuery.post( myajax.url, data, function(response) {
                    $post.parent().find('.likes_count_dislike').text(response);
                    $post.parent().find('.unlike').addClass('active');
               });
          });



          var files; 
          jQuery('input[type=file]').on('change', function(){
               files = this.files;
               console.log(files);
               alert('1');
          });
     });



