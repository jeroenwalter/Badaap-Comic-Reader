<?php

require 'ExtDirect.php';

class Server
{
    public function date( $format )
    {
        return date( $format );
    }
}

ExtDirect::provide( 'Server' );

?>
