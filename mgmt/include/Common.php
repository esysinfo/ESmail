<?php


class Common {

    public function show_result ($status = false, $message = "", $detail = array()): array {
        $msg = array(
            'status' => $status,
            'description' => array(
                'message' => $message,
                'detail' => $detail
            )
        );

        return $msg;
    }

    public function show_json_result ($status = false, $message = "", $detail = array()): string {
        $msg = array(
            'status' => $status,
            'description' => array(
                'message' => $message,
                'detail' => $detail
            )
        );

        return json_encode($msg);
    }

    public function check_allow_letter ($string = "") {
        if (! preg_match('/^[a-zA-Z0-9_\.]+@[a-zA-Z0-9-]+.*$/i', $string))
            return false;
        else
            return true;
            
    }

    public function check_accountallow_letter ($string = "") {
        if (! preg_match('/^[a-zA-Z0-9_\.]+$/i', $string))
            return false;
        else
            return true;
            
    }
}