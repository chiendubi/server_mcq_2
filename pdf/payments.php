<?php
    require_once(SERVER_PDF_ROOT . '/domPDF.php');
    class paymentsPDF {
        // use sqlModel;
        public static function createPDF($payment_id){
            $sql_model = new VanillaModel();
            $response = array('status' => 'OK', 'message' => 'none');
            $header='';
            $table ='';
            $footer ='';
            $exam_id = 0;
            $translate_label = array();
            $language = LANGUAGE;
            switch ($language) {
                case 'vi':
                    $translate_label['header_title'] = 'PHIẾU THU';
                    $translate_label['id_number_title'] = 'Số'; 
                    $translate_label['payment_date_title'] =  'Ngày'; 
                    $translate_label['pay_type_title'] =  'Hình thức'; 
                    $translate_label['sub_headerA_title'] =  'A.THÔNG TIN'; 
                    $translate_label['patient_title'] =  'Bệnh nhân'; 
                    $translate_label['phone_title'] =  'Điện thoại'; 
                    $translate_label['address_title'] =  'Địa chỉ'; 
                    $translate_label['email_title'] =  'Email'; 
                    $translate_label['sub_headerB_title'] =  'B.KẾ HOẠCH ĐIỀU TRỊ'; 
                    $translate_label['ordinal_title'] =  'STT'; 
                    $translate_label['treatment_date_title'] =  'Ngày'; 
                    $translate_label['tooth_number_title'] =  'Răng số'; 
                    $translate_label['service_title'] =  'Dịch vụ'; 
                    $translate_label['price_title'] =  'Đơn giá'; 
                    $translate_label['quantity_title'] =  'Số lượng'; 
                    $translate_label['discount_title'] =  'Giảm giá'; 
                    $translate_label['subsum_title'] =  'Thành tiền(VNĐ)'; 
                    $translate_label['total_title'] =  'Tổng cộng'; 
                    $translate_label['sub_headerC_title'] =  'C.THANH TOÁN'; 
                    $translate_label['payed_title'] =  'Đã T.Toán'; 
                    $translate_label['pay_title'] =  'Thanh toán';
                    $translate_label['debt_title'] =  'Còn nợ';
                    $translate_label['remind_title'] =  '(Phiếu thu này chỉ có giá trị lấy hóa đơn tài chính trong ngày/ This receipts only has valid to get tax invoice in the same day)'; 
                    $translate_label['patient_signature_title'] =  'Bệnh nhân'; 
                    $translate_label['creater_signature_title'] =  'Người lập'; 
                    $translate_label['accountant_signature_title'] =  'Kế toán'; 
                    $translate_label['cashier_signature_title'] =  'Thủ quỹ'; 
                    $translate_label['authorised_signature_title'] =  'Thủ trưởng'; 
                    $translate_label['signature_place_title'] =  'Ký tên'; 
                    break;
                case 'en':
                    $translate_label['header_title'] = 'RECEIPTS';
                    $translate_label['id_number_title'] = 'ID'; 
                    $translate_label['payment_date_title'] =  'Date'; 
                    $translate_label['pay_type_title'] =  'Type'; 
                    $translate_label['sub_headerA_title'] =  'A.INFORMATION'; 
                    $translate_label['patient_title'] =  'Patient'; 
                    $translate_label['phone_title'] =  'Tel'; 
                    $translate_label['address_title'] =  'Address'; 
                    $translate_label['email_title'] =  'Email'; 
                    $translate_label['sub_headerB_title'] =  'B.TREATMENT PLAN'; 
                    $translate_label['ordinal_title'] =  'No.'; 
                    $translate_label['treatment_date_title'] =  'Date'; 
                    $translate_label['tooth_number_title'] =  'Number of Tooth '; 
                    $translate_label['service_title'] =  'Service'; 
                    $translate_label['price_title'] =  'Price'; 
                    $translate_label['quantity_title'] =  'Quantity'; 
                    $translate_label['discount_title'] =  'Discount'; 
                    $translate_label['subsum_title'] =  'Total'; 
                    $translate_label['total_title'] =  'Sum'; 
                    $translate_label['sub_headerC_title'] =  'C.PAYMENT'; 
                    $translate_label['payed_title'] =  'Last payment'; 
                    $translate_label['pay_title'] =  'This payment';
                    $translate_label['debt_title'] =  'Debt';
                    $translate_label['remind_title'] =  '(Phiếu thu này chỉ có giá trị lấy hóa đơn tài chính trong ngày/ This receipts only has valid to get tax invoice in the same day)'; 
                    $translate_label['patient_signature_title'] =  'Patient'; 
                    $translate_label['creater_signature_title'] =  'Creater'; 
                    $translate_label['accountant_signature_title'] =  'Accountant'; 
                    $translate_label['cashier_signature_title'] =  'Cashier'; 
                    $translate_label['authorised_signature_title'] =  'Authorised'; 
                    $translate_label['signature_place_title'] =  'Signature'; 
                    break;
                default:
                    break;
            }
            $payment = array();
            $payment_report_sql = 'SELECT 
                payments.cdate, 
                payments.payment, 
                payments.pay_type, 
                payments.pay_code, 
                customers.id AS customer_id, 
                customers.last_name, 
                customers.first_name, 
                customers.c_clinic_code, 
                customers.address, 
                customers.email, 
                customers.phone1, 
                customers.address, 
                customers.district, 
                customers.city, 
                customers.country_pcode1, 
                examinations.id AS examination_id,
                c_clinics_translation.name AS clinic_name, 
                c_clinics_translation.address AS clinic_address, 
                c_clinics.phone AS clinic_phone,
                c_payment_type_translation.name AS payment_name 
            FROM payments '.
            ' JOIN examinations ON ( payments.examination_id =  examinations.id )' .
            ' JOIN customers ON ( customers.id =  examinations.customer_id)'.
            ' JOIN c_clinics ON ( customers.c_clinic_code =  c_clinics.code)'.
            ' JOIN c_clinics_translation ON (customers.c_clinic_code = c_clinics_translation.c_clinic_code AND c_clinics_translation.language_code = "'. LANGUAGE.'") '.
            ' JOIN c_payment_type_translation ON (c_payment_type_translation.c_payment_type_code = payments.pay_type)'.
            ' WHERE payments.id = "' . $payment_id.'"';
            $payment_report_result = $sql_model->queryWithResultSet($payment_report_sql);
            if($payment_report_result['status'] == 'OK'){
                $payment_report_data = $payment_report_result['info']['rows'][0];
                $exam_id = $payment_report_data['examination_id'];
                $pdate=date_create($payment_report_data['cdate']);
                $payment_date = date_format($pdate,"d/m/Y");
                $payment_formatted = number_format($payment_report_data['payment'], 0, ",", ".");

                $width = 5;
                $padded = str_pad((string)$payment_report_data['customer_id'], $width, "0", STR_PAD_LEFT); 
                $customer_code = $payment_report_data['c_clinic_code'] . '_' . $padded .'_'. CUSTOMER_TYPE; 
                if( $payment_report_data['district']){
                    $customer_address = $payment_report_data['address'] .', '. $payment_report_data['district'] .', '. $payment_report_data['city'];
                }else{
                    $customer_address = $payment_report_data['address'] .', '. $payment_report_data['city'];            
                }
            }

        $exam_sql = 'SELECT 
        methods.*, 
        c_methods_translation.name 
        FROM methods
        LEFT JOIN c_methods_translation ON (methods.c_method_code = c_methods_translation.c_method_code  AND c_methods_translation.language_code = "'. LANGUAGE . '")
        WHERE methods.examination_id = "'. $exam_id. '" AND methods.confirm = 1';
        $exam = $sql_model->queryWithResultSet($exam_sql);

        $payment_total_sql = 'SELECT * FROM payments WHERE examination_id =' . $exam_id;
        $payment_total = $sql_model->queryWithResultSet($payment_total_sql);

        if(count($exam['info']['rows']) > 0){
            for ($i = 0; $i < count($exam['info']['rows']); $i++) {
                $item = $exam['info']['rows'][$i];
                $c_treatment_item_code_string = '';
                $c_treatment_item_code = $item['c_treatment_item_code'];
                $result = explode('|',  $c_treatment_item_code);
                if(count($result) > 0){
                    $out = array();
                    $temp = array();
                    foreach($result as $c){
                        $temp[] = $sql_model->queryWithOneResultSet('
                            SELECT c_treatment_item_translation.name FROM c_treatment_item_translation
                            WHERE c_treatment_item_translation.c_treatment_item_code = "'.$c.'" AND c_treatment_item_translation.language_code = "'.LANGUAGE.'"
                        ');
                    };
                    foreach($temp as $key => $value){
                        $out[] = $value['name']; 
                    }
                    for ($j = 0; $j < count($out); $j++) {
                        $c_treatment_item_code_string .= strval($out[$j]);
                        if(count($out) - 1 != $j){
                            $c_treatment_item_code_string .=  ', ';
                        }
                    }
                }
                $item['c_treatment_item_code_string'] = $c_treatment_item_code_string;
                $exam['info']['rows'][$i] = $item;
            }
        }
        $header = 
        '<table width="100%" height="80px" style="margin-top:-35px">
            <tbody>
                <td width="12%">
                    <img src="' . SYSTEM_IMAGE_PATH .'favicon.jpg" style="height: 75px; width: 75px">
                </td>
                <td style="font-size: 9;">
                    <strong>' . $payment_report_data['clinic_name'] . '</strong><br>
                    ' . $payment_report_data['clinic_address'] . '<br>
                    ' . $payment_report_data['clinic_phone'] .'
                </td>
            </tbody>
        </table>
        <table width="100%" height="80px" style="margin-top:-15px">
            <tbody style="font-size: 9;">
                <tr> 
                    <td colspan="6" style="font-size: 14; text-align: center"><strong>'.$translate_label['header_title'].'</strong></td>
                </tr>
                <tr>
                    <td colspan="2" style="font-size: 9; text-align: left">'.$translate_label['id_number_title'].': ' . $payment_report_data['pay_code'] . '</td>
                    <td colspan="2" style="font-size: 9; text-align: center; padding-left: 20px">'.$translate_label['payment_date_title'].': ' . $payment_date . '</td>
                    <td colspan="2" style="font-size: 9; text-align: right">'.$translate_label['pay_type_title'].': '. $payment_report_data['payment_name'] .'</td>
                </tr>
            </tbody>
        </table>
        <table width="100%" height="80px" style="margin-left: 0px;">
            <tbody>
                <tr>
                    <td width="40%"  style="font-size: 9; margin: 10px 0;"><strong>'.$translate_label['sub_headerA_title'].'</strong></td>
                    <td width="60%" style="font-size: 9; text-align: right">' . $customer_code . '</td>
                </tr>
                <tr>
                    <td width="70%" style="font-size: 9;">'.$translate_label['patient_title'].': ' . $payment_report_data["last_name"] . ' ' . $payment_report_data['first_name'] . '</td>
                    <td width="30%" style="font-size: 9;">'.$translate_label['phone_title'].': +' . $payment_report_data["country_pcode1"] . ' ' . $payment_report_data["phone1"] . '</td>
                </tr>
                <tr>
                    <td width="70%" style="font-size: 9;">'.$translate_label['address_title']. ': ' . $customer_address. '</td>
                    <td width="30%" style="font-size: 9;">'.$translate_label['email_title'].': ' . $payment_report_data["email"] . '</td>
                </tr>
            </tbody>
        </table>';

        $method_total = 0;
        $tbody = '';
        for ($i = 0; $i < count($exam['info']['rows']); $i++) {
            $item = $exam['info']['rows'][$i];
            $currency = '';
            $method_total = $method_total + $item["payment"];
            $cdate = date_create($item["cdate"]);
            $method_date = date_format($cdate,"d/m/Y");
            $item["fee"] = number_format($item["fee"], 0, ",", ".");
            $item["payment"] = number_format($item["payment"], 0, ",", ".");
            if($item["currency"] == 'USD'){
                $currency = '$'; 
            }
            $temp = 
            '<tr>
                <td width="4%" align="center">' . ($i + 1) . '</td>
                <td width="12%" align="center">' . $method_date  . '</td>
                <td width="20%" align="center">' . str_replace ( '|' , ', ' , $item["c_treatment_item_code_string"]) .'</td>
                <td width="28%" align="left">' . $item["name"] . '</td>
                <td width="12%" align="right">' . $currency . ' ' . $item["fee"] . '</td>
                <td width="5%" align="center">' . $item["unit"] . '</td>
                <td width="5%" align="center">' . $item["discount"] . '</td>
                <td width="14%" align="right">' . $item["payment"] . '</td>
            </tr>';
            $tbody .= $temp;
        }

        $last_payment_total = 0;
        $last_payment_current = 0;
        $debt_payment = 0;
        for ($j = 0; $j < count($payment_total['info']['rows']); $j++) {
            $item = $payment_total['info']['rows'][$j];
            $last_payment_total = $last_payment_total + $item["payment"];
        }

        $last_payment_current = number_format($last_payment_total -  $payment_report_data['payment'], 0, ",", ".");
        $debt_payment = number_format($method_total -  $last_payment_total, 0, ",", ".");

        $method_total_str = Utility::convert_number_to_words($method_total);
        $table = 
        '<h3 style="font-size: 9; margin: 10px 0;"><strong>'.$translate_label['sub_headerB_title'].'</strong></h3>
        <table width="100%" id="prescription_items" cellpadding="2" cellspacing="0" style="font-size: 8" border="1">
            <thead>
                <tr>
                    <th  width="4%" align="center">'.$translate_label['ordinal_title'].'</th>
                    <th  width="12%" align="center">'.$translate_label['treatment_date_title'].'</th>
                    <th  width="20%" align="center">'.$translate_label['tooth_number_title'].'</th>
                    <th  width="28%" align="center">'.$translate_label['service_title'].'</th>
                    <th  width="12%" align="center">'.$translate_label['price_title'].'</th>
                    <th  width="5%" align="center">'.$translate_label['quantity_title'].'</th>
                    <th  width="5%" align="center">'.$translate_label['discount_title'].'%</th>
                    <th  width="14%" align="center">'.$translate_label['subsum_title'].'</th>
                </tr>
            </thead>
            <tbody>
                ' . $tbody . '
                <tr>
                    <td colspan="7" align="center" style="font-size: 9;"><strong>'.$translate_label['total_title'].'</strong></td>
                    <td align="right" style="font-size: 9;"><strong>' .  number_format($method_total, 0, ",", ".")  . '</strong></td>
                </tr>
            </tbody>
        </table> 
        <h3 style="font-size: 9;  margin: 10px 0;"><strong>'.$translate_label['sub_headerC_title'].'</strong></h3>
        <table width="100%" style="font-size: 9; margin: -5px 0;"">
            <thead>
                <tr>
                    <td style="font-size: 9;" align="center">'.$translate_label['payed_title'].'</t>
                    <td style="font-size: 9;" align="center">'.$translate_label['pay_title'].'</th>
                    <td style="font-size: 9;" align="center">'.$translate_label['debt_title'].'</th>
                </tr>
            </thead>
            <tbody>
                <tr> 
                    <td style="font-size: 9;" align="center"><strong>'. $last_payment_current .'</strong></td>
                    <td style="font-size: 9;" align="center"><strong>' . $payment_formatted . '</strong></td>
                    <td style="font-size: 9;" align="center"><strong>'. $debt_payment .'</strong></td>
                </tr>
                <tr> 
                    <td colspan="3" width="100%" style="font-size: 8; padding-top:0px;">'.$translate_label['remind_title'].'</td>
                </tr>
            </tbody>
        </table>';

        $footer = 
        '<table width="100%" style="font-size: 8.4; margin-top: 30px;" >
            <thead>
                <tr>
                    <th  width="18%" align="center">'.$translate_label['patient_signature_title'].'</th>
                    <th  width="20%" align="center">'.$translate_label['creater_signature_title'].'</th>
                    <th  width="20%" align="center">'.$translate_label['accountant_signature_title'].'</th>
                    <th  width="18%" align="center">'.$translate_label['cashier_signature_title'].'</th>
                    <th  width="24%" align="center">'.$translate_label['authorised_signature_title'].'</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td width="18%" align="center" style="padding-top: 50px">'.$translate_label['signature_place_title'].'</td>
                    <td width="20%" align="center" style="padding-top: 50px">'.$translate_label['signature_place_title'].'</td>
                    <td width="20%" align="center" style="padding-top: 50px">'.$translate_label['signature_place_title'].'</td>
                    <td width="18%" align="center" style="padding-top: 50px">'.$translate_label['signature_place_title'].'</td>
                    <td width="24%" align="center" style="padding-top: 50px">'.$translate_label['signature_place_title'].'</td>
                </tr>
            </tbody>
        </table>';

        $html = 
        '<!DOCTYPE html>
            <html>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <head>
                <style>
                    * { font-family: DejaVu Sans !important;
                    }
    /*                
                    #drill tr:nth-child(odd){background-color: #f9f9f9}
                    table#drill, #drill th, #drill td {
                        border: 1px solid #ddd;
                    }
    */
                    th {
                        height: 30px;
                    }
                    p {
                        font-size: 10;
                    }
                    img.ss_img {
                        margin-left: 20%;
                        width: 60%;
                    }
                </style>
            </head>
            <body>
                <div style="width: 95%; position: relative; margin: 10 auto";>'. $header . $table . $footer .'</div>
              </body>
            </html>';

            // Export
            $type = "Payment";
            $filename = stripVN(preg_replace('/\s+/', '', $payment_report_data['last_name']).''.preg_replace('/\s+/', '', $payment_report_data['first_name']));
            $response = domMakePDF::create($type, $html, $filename);
            return $response;
        }
    }
?>