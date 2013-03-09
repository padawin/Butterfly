<?php

/**
 *
 * Abstract class of the components (modules, widgets...)
 *
 * @abstract
 * @author Ghislain Rodrigues <ghislain.rodrigues@hotmail.fr>
 *
 */
abstract class Butterfly_Component
{

    /**
     * @var View
     * @access protected
     */
    protected $_view;

    /**
     *
     * Construct of the class
     *
     * @param Layout $layout Layout of the page
     * @access public
     *
     */
    public function __construct($layout)
    {
        $this->_view = Butterfly_Factory::create('View');
        $this->_view->setLayout($layout);
    }

    /**
     *
     * Getter of the view
     *
     * @return View
     * @access public
     *
     */
    public function getView(){
        return $this->_view;
    }

    /**
     *
     * Setter of the view
     *
     * @param View $v
     * @return Component Return the Component
     * @access public
     *
     */
    public function setView($v){
        $this->_view->setFile($v);
        return $this;
    }

    /**
     *
     * Setter of the view base
     *
     * @param String $path
     * @return Component Return the Component
     * @access public
     *
     */
    public function setViewBase($path){
        $this->_view->setBase($path);
        return $this;
    }

    /**
     *
     * Function to check if the component's view's file is defined
     *
     * @return Boolean true if the file is setted, false else
     * @access public
     *
     */
    public function hasViewFile()
    {
        return $this->_view->getFile() != null;
    }

    /**
     *
     * Display the view of the component
     *
     * @access public
     *
     */
    public function render(){
        echo $this->_view->render();
    }

    /**
     *
     * return true if the view file exists
     *
     */
    public function viewExists()
    {
        $extensions = array('php', 'html');
        for ($i = 0 ; $i < count($extensions) ; $i ++) {
            if (is_file($this->_view->getBase() . $this->_view->getFile() . '.' . $extensions[$i])) {
                return true;
            }
        }
        return false;
    }
}
