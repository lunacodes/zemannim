<script type="text/javascript">
    jQuery(document).ready( function($) {

  $.ajax({
    url: "https://hasepharadi.com/staging",
    success: function( data ) {
      alert( 'Your home page has ' + $(data).find('div').length + ' div elements.');
    }
  })

})
</script>




<?php 
// This would normally be enqueued as a file, but for the sake of ease we will just print to the footer
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
 


// This bit is a special action hook that works with the WordPress AJAX functionality. 
add_action( 'wp_ajax_example_ajax_request', 'example_ajax_request' );
function example_ajax_request() {
  
    // The $_REQUEST contains all the data sent via AJAX from the Javascript call
    if ( isset($_REQUEST) ) {
      
        $fruit = $_REQUEST['fruit'];
          
        // This bit is going to process our fruit variable into an Apple
        if ( $fruit == 'Banana' ) {
            $fruit = 'Apple';
        }
      
        // Now let's return the result to the Javascript function (The Callback) 
        echo $fruit;        
    }
      
    // Always die in functions echoing AJAX content
   die();
}
  
