<?php
require_once(SERVER_ROOT . '/lib/dompdf_0_8_3/autoload.inc.php');
use Dompdf\Options;
use Dompdf\Dompdf;
class domMakePDF {
    public static function create($type, $html, $filename='', $stream=FALSE) {
        // set basic data
        $response = array('status' => 'ERROR', 'message' => 'none');
        $options = new Options();
        $options->set('enable_html5_parser', true);
        $options->set('defaultFont', 'Helvetica');
        $dompdf = new Dompdf($options);
        $dompdf->load_html($html);
        $dompdf->render();
        $dompdf->setPaper('A5', 'orientation');
        if ($stream) {
            $dompdf->stream($filename);
            $response = array('status' => 'OK', 'message' => 'File ' . $filename . ' had been saved to system download folder.');
        } else {
            $output = $dompdf->output();
            $new_string = substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 5)), 0, 5);
            $new_file_name = date("YmdHis").'_'.$new_string.'_'.$filename;
            $filename = $new_file_name.'_'.$type.'.pdf';
            $result = file_put_contents(PRODUCT_IMAGE_PATH . $filename, $output);
            $response = array(
                'status' => 'OK', 
                'message' => 'File <strong>' . $filename . '</strong> had been saved to system.',
                'filename' => $filename
            );
        }
        return $response;
    }
}
?>