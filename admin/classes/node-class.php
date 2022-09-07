<?php
class node
{
    private $text = '';                                      // the text (name) of the node
    private $children = array();                             // list of the children of the node

    function __construct($text)
    {                            // constructor. sets the text
        $this->text = $text;
    }

    function addChild(&$ref)
    {                               // add a child
        $this->children[] = $ref;                              // store reference to child in $this->children array property
    }

    function getText()
    {                                     // get text of the node
        return $this->text;                                    // retrieve textdomain
    }

    function getChildren()
    {                                 // get children of the node
        return $this->children;                                // fetch & deliver array
    }

    function __toString()
    {                                  // magic function for conversion to string
        return $this->getText();                               // is an alias for $this->getText
    }
}
