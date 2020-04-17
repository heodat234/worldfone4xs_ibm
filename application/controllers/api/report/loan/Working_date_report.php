<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Working_date_report extends WFF_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->dir = '/mnt/nas/upload_file/working_date_report';
    }

    public function read()
    {
        header('Content-type: application/json');
        $data = [];
        $listFiles = scandir($this->dir);
        $i = 0;
        foreach ($listFiles as $key => $file) {
            if ($key == 1 || $key == 0) {
                continue;
            }

            $data[$i]['filename'] = $file;
            $data[$i]['file_path'] = $file;
            $i++;
        }
        $result = array('data' => $data, 'total' => count($data));

        echo json_encode($result);
    }

    public function download()
    {
        $file_path = urldecode($_GET['data']);

        $file = $this->dir .'/'. $file_path;

        if (!file_exists($file)) {
            die('file not found');
        } else {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            flush();
            readfile($file);
        }
    }
}