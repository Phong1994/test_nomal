<?php

class LECM_Controller_Session
    extends LECM_Controller
{
    public function refresh()
    {
        LECM_Session::refresh();
        $response = array(
            'result' => 'success',
            'time' => time()
        );
        echo json_encode($response);
    }
}