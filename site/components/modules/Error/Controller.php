<?php

class Error_Controller extends Butterfly_Component_Module
{

    public function otherAction()
    {
        $this->_view->errorMessage = Butterfly_Session::get('error');
        Butterfly_Session::delete('error');
    }

    public function notfoundAction()
    {
        header("HTTP/1.0 404 Not Found");
    }
}
