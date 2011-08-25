<?php

/**
 *  Controller to load the models libraries and views for the dice examples.
 *  
 *  (this is a quick project but really.. just strip this thing and put the meat and potatoes
 *  in code igniter or something)
 *  
 *  @author T Pierce <tyler.pierce@gmail.com>
 */

class Controller
{
    /**
     *  Base path of dice games
     */
    private $_path = '';


    /**
     *  Constructor
     */
    function __construct ($path = '')
    {
        $this->_path = $path;
    }
    
    /**
     *  Load a model, returning a class called from the models directory
     *  
     *  @param string modelName
     *  @return model object
     */
    protected function loadModel ($modelName)
    {
        include $this->_path . 'models/' . $modelName . '.php';

        return new $modelName;
    }

    /**
     *  Load a view, immediately spilling it's output, unless called for as a string
     *  by giving true for returnResult
     *  
     *  @param string viewName
     *  @param array params
     *  @param boolean returnResult
     *  @return true if output, or view as a string if requested
     */
    protected function loadView ($viewName, Array $params = array(), $returnResult = false)
    {
        // extracting key/val params to php vars
        extract($params);
        $cntr_out = false;

        if (!$returnResult)
        {            
            include $this->_path . 'views/' . $viewName . '.php';

            $cntr_out = true;
        }
        else
        {
            ob_start();

            include $this->_path . 'views/' . $viewName . '.php';

            $cntr_out = ob_get_contents();

            ob_end_clean();
        }

        return $cntr_out;
    }
}

