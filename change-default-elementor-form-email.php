<?php
 
add_filter( 'option_admin_email', function( $value ) {
    $caller = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 4 );
    if ( isset( $caller[3] ) && isset( $caller[3]['file'] ) && false !== strpos( $caller[3]['file'], 'forms/actions/email') ) {
        //this is most likly comming from elementor pro forms module
        //so you need to return the client's email 
        return 'other@email.com';
    }
    // default value
    return $value;
} );
