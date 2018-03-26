function add_this_script_footer(){ ?>
  
<script>
    jQuery(document).ready(function($) {
      
        // This is the variable we are passing via AJAX
        var fruit = 'Banana';
          
        // This does the ajax request (The Call).
        $.ajax({
            url: ajaxurl, // Since WP 2.8 ajaxurl is always defined and points to admin-ajax.php
            data: {
                'action':'example_ajax_request', // This is a our PHP function below
                'fruit' : fruit // This is the variable we are sending via AJAX
            },
            success:function(data) {
        // This outputs the result of the ajax request (The Callback)
                window.alert(data);
            },  
            error: function(errorThrown){
                window.alert(errorThrown);
            }
        });   
                   
    });
</script>
<?php } 

add_this_script_footer();

