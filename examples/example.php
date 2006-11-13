<?php
/**
*   Example for Gtk_VarDump
*   @author Christian Weiske
*/
require_once 'Gtk/VarDump.php';


class Test
{
    var $color  = 'blue';
    var $foo    = 'bar';
    var $self   = null;
    var $server = null;

    function Test()
    {
        $this->self   =& $this;
        $this->server =& $_SERVER; 
    }

    function doNothing() 
    {
        //we're doing nothing here
    }
}

new Gtk_VarDump(new Test());

?>