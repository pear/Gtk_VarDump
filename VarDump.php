<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2002 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors:  Alan Knowles <alan@akbkhome.com>                           |
// +----------------------------------------------------------------------+
//
// $Id$
//
// GTK var dump tool
//

/**
* simple class to do VarDump in GTK
*
* @abstract 
* A simple class that does a regedit type interface for viewing
* data in a tree like format.
*
* usage:
* $data = array(1,2,3,4);
* $x = new GTK_VarDump($data,'test array');
*
* thats it!!
*
* @version    $Id$
*/
class Gtk_VarDump {
    
    /**
    * aliased array of node points
    * (id => point in tree)
    *
    * @var array
    * @access private
    */
    var $_nodes = array();
    /**
    * aliased array of gtk nodes (id => gtknode)
    *
    * @var array
    * @access private
    */

    var $_gtkNodes = array();
    
    
    /**
    * Constructor
    *
    * This is all you need!!!
    * 
    * 
    * @param   array|object    tree to display
    * @param   string base name of tree
    * 
    * @access   public
    */
  
    
    function Gtk_VarDump(&$tree,$baseName = 'BASE') {
        // it thought there was a rountine in pear somewhere to do this..
        
        if (!extension_loaded('php-gtk')) {
             dl('php_gtk.' .PHP_SHLIB_SUFFIX    ); 
        }
        $this->_loadInterface($baseName);
        $this->_gtkTree->clear();
        $n = $this->_buildTree($tree,0,$baseName,true);
        $this->_addChildren($n,2);
        $this->_display($tree);
        gtk::main();
    }
     /**
    * the main glade object
    *
    * @access private
    */
    var $_glade;  
    /**
    * the main gtk window
    *
    * @access private
    */
    var $_gtkWindow;    
    /**
    * the main gtk Tree
    *
    * @access private
    */
    var $_gtkTree;
    /**
    * the main gtk List
    *
    * @access private
    */

    var $_gtkList;

        
    /**
    * Set up the interface
    * (loads interface from a glade file - VarDump/interface.glade)
    * @access   private
    * @param    string $title of window
    */
  
    function _loadInterface($title) {
        $this->_glade = new GladeXML(dirname(__FILE__).'/VarDump/interface.glade');
        
        $this->_gtkTree = $this->_glade->get_widget('ctree');
        $this->_gtkTree->connect_object_after('tree-expand',   array(&$this, '_expandTree'));
        $this->_gtkTree->connect_object_after(       'tree-select-row', array(&$this, '_selectRow'));
        $this->_gtkTree->set_column_width(1, 80);
        $this->_gtkTree->set_column_auto_resize(0,true);
        $this->_gtkList = $this->_glade->get_widget('list');
        
        $OK = $this->_glade->get_widget('ok');
        $OK->connect('clicked', array(&$this,'_done'));
        
        $this->_gtkWindow = $this->_glade->get_widget('window');
        $this->_gtkWindow->connect('destroy', array(&$this,'_done'));
        $this->_gtkWindow->connect('delete_event', array(&$this,'_deleteEvent'));
        $this->_gtkWindow->set_title("Gtk_VarDump :: $title");
        $this->_setfont('text','-*-helvetica-bold-r-normal-*-*-120-*-*-p-*-iso8859-1');
    }
    /**
    * Set up the font for a widget
    * 
    * @access   private
    * @param   string $widgetname name of widget
    * @param   string $fontname  name of font
    */
    
    function _setFont($widgetname,$fontname) {
        $font = gdk::font_load($fontname);
        
        $widget = $this->_glade->get_widget($widgetname); 
        $oldstyle = $widget->get_style();
        $newstyle = $oldstyle->copy();
        $newstyle->font = $font;
        $widget->set_style($newstyle);
    }
    
    
    /**
    * Delete event callback
    *
    * @access   private
    */
    
    function _deleteEvent() {
        return false;
    }
    /**
    * Quit/Done callback
    *
    * @access   private
    */

    function _done() {
        $this->_gtkWindow->hide();
        $this->_gtkWindow->destroy();
        gtk::main_quit();
        
    }
     /**
    * Counter used for node id creation
    *
    * @var int
    * @access private
    */
   
    var $n = 0; // node counter
    /**
    * Children of a node (to be deleted on expansion
    *
    * @var array
    * @access private
    */
    var $_children  = array();
  
    /**
    * build the Tree 
    *
    *
    * @param object|array node, the data to show
    * @param int id of the parent.
    * @param string name of node (text)
    * @param bool expanded is it expanded - 
    * @access   private
    */

  
  
  
    function _buildTree(&$node,$parentId, $name,$expanded = false) {
        
        // make the node for this object or array..
        
        switch (gettype($node)) {
            case 'object';
                $col_a = $name;
                $col_b = get_class($node);
                break;
            case 'array':
                $col_b = 'array';
                $col_a = $name;
                break;
            default:
                return;
        }
        
        
        
        $this->n++;
        $n = $this->n;
        $this->_nodes[$n] = &$node;
       // echo "ADD NODE($n) TO : $parentId\n";
        $this->_gtkNodes[$n] = $this->_gtkTree->insert_node(
            @$this->_gtkNodes[$parentId],   // parent
            null,   // sibling
            array($col_a. ' ',$col_b. ' '),  //  text to display
            0,      // spacing
            null,   // pixmapclosed
            null,   // maskclosed
            null,   // pixmapopen
            null,   // maskopen
            false,  //is_leaf
            $expanded // expanded
            );
     
        $this->_children[$parentId][] = $n;
        
        $this->_gtkTree->node_set_row_data($this->_gtkNodes[$n], $n);
       
        // then find out all it's children...
        
        return $n;
    }
      
    /**
    * Add children to a tree
    *
    * and removes old children after new ones are added..
    * 
    * 
    * @param   int $n   base to build on
    * @param   int $decay so it only goes down one level
    * 
    * @access   private
    */
    
    function _addChildren($n,$decay) {
        //echo "ADDCHILDREN : $n, $decay\n";
        if ($decay < 0) {
            return;
        }
        $remove = @$this->_children[$n];
        
        $this->_children[$n] = array();
        
        $node = &$this->_nodes[$n];
        $type = gettype($node);
        switch ($type) {
            case 'object':
                $parts = array_keys(get_object_vars($node));
                foreach($parts as $k) {
                    if (!isset($node->$k)) {
                        continue;
                    }
                    $nn = $this->_buildTree($node->$k,$n,$k);
                    if ($nn) {
                        $this->_addChildren($nn,$decay - 1);
                    }
                    
                }
                break;
            case 'array':
                $parts = array_keys($node);
                foreach($parts as $k) {
                    $nn = $this->_buildTree($node[$k],$n, $k);
                    if ($nn) {
                        $this->_addChildren($nn,$decay - 1);
                    }
                }
                break;
                
                    
        }   
      
        if (is_array($remove)) {
            foreach($remove as $k) {
                 $this->_gtkTree->remove_node($this->_gtkNodes[$k]);
                 
                unset($this->_gtkNodes[$k]);
                unset($this->_nodes[$k]);
            }
        }
                        
    }
    
    /**
    * call back for a node being expanded
    * 
    * @param   gtkNode $gtknode  node that was expanded
    * @access   private
    */
    
    function _expandTree($gtkNode) {
        $id = $this->_gtkTree->node_get_row_data($gtkNode);
        $this->_addChildren($id,1);
      
         
    }
    /**
    * call back for a node selected
    * 
    * @param   gtkNode $gtknode  node that was selected
    * @access   private
    */
    
    function _selectRow($gtkNode) {
        $id = $this->_gtkTree->node_get_row_data($gtkNode);
        $this->_display($this->_nodes[$id]);
    }
    /**
    * display the data (except objects/arrays) in the right hand side..
    * 
    * @param   object|array $node  data to display
    * @access   private
    */

    function _display(&$node) {
        $widget = $this->_glade->get_widget('text');
        $this->_gtkList->clear();
        $type = gettype($node);
        switch ($type) {
            case 'object':  
                $widget->set_text('Object: '. get_class($node));
                
                $parts = array_keys(get_object_vars($node)); 
                foreach($parts as $k) {
                    switch (gettype($node->$k)) {
                        case 'object':
                        case 'array':
                            continue;
                        default:
                            $this->_gtkList->append(array(' '.$k,gettype($node->$k),' '.$node->$k));
                    }
                }
                break;
            case 'array':
                $widget->set_text('Array');
                $parts = array_keys($node); 
                foreach($parts as $k) {
                    switch (gettype($node[$k])) {
                        case 'object':
                        case 'array':
                            continue;
                        default:
                            $this->_gtkList->append(array(' '.$k,' '.gettype($node[$k]),' '.$node[$k]));
                    }
                }
        }
    }
     
    
    
    
}
/*
$t = new StdClass;
$t->test = $GLOBALS;
// test code!
new GTK_VarDump($t,'test');
*/



?>