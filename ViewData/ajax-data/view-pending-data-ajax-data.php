<?php
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if ($errno === E_NOTICE || $errno === E_WARNING) {
        file_put_contents(__DIR__ . '/_ajax_error_log.txt',
            date('Y-m-d H:i:s') . " [$errno] $errstr in $errfile:$errline\n", FILE_APPEND);
        return true;
    }
    return false;
});

header('Content-Type: application/json');

require '../../vendor/autoload.php';
include "../../Config/config.php";
include "../../Lib/lib.php";

$app = new Solvers\Dsql\Application();

$cn = ConnectDB();

if (!$cn) {
    header('Content-Type: text/plain');
    die("Database connection failed.\n\n" . print_r(sqlsrv_errors(), true));
}

if ($_REQUEST['statusCode'] != '') {
    $DataStatus = xss_clean($_REQUEST["statusCode"]);
}
if ($_REQUEST['frmID'] != '') {
    $DataFromID = xss_clean($_REQUEST["frmID"]);
}
if ($_REQUEST['lci'] != '') {
    $DataCompanyID = xss_clean($_REQUEST["lci"]);
}
if ($_REQUEST['lun'] != '') {
    $LoggedUserName = xss_clean($_REQUEST["lun"]);
}
if ($_REQUEST['luid'] != '') {
    $LoggedUserID = xss_clean($_REQUEST["luid"]);
}
if ($_REQUEST['divCode'] != '') {
    $DivisionCode = xss_clean($_REQUEST["divCode"]);
}
if ($_REQUEST['distCode'] != '') {
    $DistrictCode = xss_clean($_REQUEST["distCode"]);
}
if ($_REQUEST['chkAll'] != '') {
    $DataChkAll = xss_clean($_REQUEST["chkAll"]);
}

$qry = "";

if ($DataChkAll) {
    $qry = "SELECT xfr.id, xfr.SampleHHNo, xfr.PSU, ui.UserName, ui.id as userid, ui.FullName, ui.MobileNumber, xfr.DataName, xfr.DeviceID, xfr.EntryDate, xfr.FormGroupId, xfr.IsApproved, xfr.XFormsFilePath, COALESCE(xfr.IsEdited, 0) AS IsRowEdited, pl.DivisionName, pl.DistrictName FROM xformrecord xfr  JOIN userinfo ui ON xfr.UserID = ui.id JOIN PSUList pl ON pl.PSUUserID = ui.id AND xfr.PSU = pl.PSU WHERE xfr.IsApproved = $DataStatus AND xfr.FormId = $DataFromID AND xfr.CompanyId = $DataCompanyID";
} elseif ($DistrictCode) {
    $qry = "SELECT xfr.id, xfr.SampleHHNo, xfr.PSU, ui.UserName, ui.id as userid, ui.FullName, ui.MobileNumber, xfr.DataName, xfr.DeviceID, xfr.EntryDate, xfr.FormGroupId, xfr.IsApproved, xfr.XFormsFilePath, COALESCE(xfr.IsEdited, 0) AS IsRowEdited, pl.DivisionName, pl.DistrictName FROM xformrecord xfr JOIN userinfo ui ON xfr.UserID = ui.id JOIN PSUList pl ON pl.PSUUserID = ui.id AND xfr.PSU = pl.PSU WHERE xfr.IsApproved = $DataStatus AND xfr.FormId = $DataFromID AND xfr.CompanyId = $DataCompanyID AND pl.DivisionCode = $DivisionCode AND pl.DistrictCode = $DistrictCode";
} else {
    $qry = "SELECT xfr.id, xfr.SampleHHNo, xfr.PSU, ui.UserName, ui.id as userid, ui.FullName, ui.MobileNumber, xfr.DataName, xfr.DeviceID, xfr.EntryDate, xfr.FormGroupId, xfr.IsApproved, xfr.XFormsFilePath, COALESCE(xfr.IsEdited, 0) AS IsRowEdited, pl.DivisionName, pl.DistrictName FROM xformrecord xfr JOIN userinfo ui ON xfr.UserID = ui.id JOIN PSUList pl ON pl.PSUUserID = ui.id AND xfr.PSU = pl.PSU WHERE xfr.IsApproved = $DataStatus AND xfr.FormId = $DataFromID AND xfr.CompanyId = $DataCompanyID AND pl.DivisionCode = $DivisionCode";
}

$resQry = $app->getDBConnection()->fetchAll($qry);

$data = array();
$il = 1;

 foreach ($resQry as $row) {
     $RecordID = $row->id;
     $HhNo = $row->SampleHHNo;
     $PSU = $row->PSU;

     $UserID = $row->userid;
     $UserName = $row->UserName;
     $UserFullName = $row->FullName;
     $UserData = "$UserFullName ($UserName/$UserID)";

     $UserMobileNo = $row->MobileNumber;
     $UserMobileNo = whatsAppLink($UserMobileNo);

     $DataName = $row->DataName;
     $XFormsFilePath = $row->XFormsFilePath;
     $DeviceID = $row->DeviceID;

     $EntryDate = '';
     if (!empty($row->EntryDate)) {
         $EntryDate = date('d-m-Y H:i:s', strtotime($row->EntryDate));
     }

     $IsApproved = $row->IsApproved;

     $DataStatus = GetDataStatus($IsApproved);

     $DivisionName = $row->DivisionName;
     $DistrictName = $row->DistrictName;

     $IsEdited = $row->IsRowEdited;

     $Duration = 'N/A';

     $SubData = array();

     $actions = "";

     $actions = "<div style= \"display: flex; align-items: center; justify-content: center;\">

                <button title=\"$btnTitleView\" type=\"button\" class=\"simple-ajax-modal btn btn-outline-primary\" style=\"display: inline-block;margin: 0 1px;\" data-bs-toggle=\"modal\" data-bs-target=\"#viewDataModal\" onclick=\"ShowDataDetail('$DataFromID','$RecordID', '$IsApproved', '$PSU', '$LoggedUserID', '$UserID', '$XFormsFilePath')\"><i class=\"fas fa-eye\"></i></button>
                    
                    <button title=\"$btnTitleNotice\" type=\"button\" class=\"btn btn-outline-secondary\" style=\"display: inline-block;margin: 0 1px;\" data-bs-toggle=\"modal\" data-bs-target=\"#sendNoticeModal$RecordID\"><i class=\"fas fa-bell\"></i></button>
                </div>
                <script type=\"text/javascript\">
                    function ShowDataDetail(dataFromID,recordID, isAproved, psu, loggedUserID, agentID, XFormsFilePath, data) {
                            $.ajax({
                                url: 'ViewData/ajax-data/data-detail-view-pending-data.php',
                                method: 'GET',
                                datatype: 'json',
                                data: {
                                    dataFromID: dataFromID,
                                    id: recordID,
                                    status: isAproved,
                                    psu: psu,
                                    loggedUserID: loggedUserID,
                                    agentID: agentID,
                                    XFormsFilePath: XFormsFilePath
                                },
                                success: function (response) {
                                    //alert(response);
                                    $('#dataViewDiv').html(response);
                                }
                            }); 
                        return false;
                    }
                    function ShowDataDetailForViewOnly(dataFromID,recordID, isAproved, psu, loggedUserID, agentID, XFormsFilePath, data) {
                            $.ajax({
                                url: 'ViewData/ajax-data/data-detail-view-pending-data-for-view-only.php',
                                method: 'GET',
                                datatype: 'json',
                                data: {
                                    dataFromID: dataFromID,
                                    id: recordID,
                                    status: isAproved,
                                    psu: psu,
                                    loggedUserID: loggedUserID,
                                    agentID: agentID,
                                    XFormsFilePath: XFormsFilePath
                                },
                                success: function (response) {
                                    //alert(response);
                                    $('#dataViewDivForViewOnly').html(response);
                                }
                            }); 
                        return false;
                    }
                </script>
                
                <!-- View Data Modal-->
                <div class=\"modal fade bd-example-modal-xl\" id=\"viewDataModal\" tabindex=\"-1\" aria-labelledby=\"editDataModalLabel\" aria-hidden=\"true\">
                  <div class=\"modal-dialog modal-xl\">
                    <div id=\"dataViewDiv\" class=\"modal-content\">
                      
                    </div>
                  </div>
                </div>
                
                <!-- View Data Modal For View Only-->
                <div class=\"modal fade bd-example-modal-xl\" id=\"viewDataModalForViewOnly\" tabindex=\"-1\" aria-labelledby=\"editDataModalLabelForViewOnly\" aria-hidden=\"true\">
                  <div class=\"modal-dialog modal-xl\">
                    <div id=\"dataViewDivForViewOnly\" class=\"modal-content\">
                      
                    </div>
                  </div>
                </div>";

     $actions .= " 
                 <!-- Send Notification Modal-->
                <div class=\"modal fade\" id=\"sendNoticeModal$RecordID\" tabindex=\"-1\" aria-labelledby=\"editDataModalLabel\" aria-hidden=\"true\">
                  <div class=\"modal-dialog\">
                    <div class=\"modal-content\">
                      <div class=\"modal-header\">
                      <h5 class=\"modal-title\" id=\"editDataModalLabel\">Send Message</h5>
                        <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>
                      </div>
                      <div class=\"modal-body\">
                        <form id=\"editForm\" method=\"POST\" action=\"\">
                            <div class=\"form-group\">
                                <label for=\"UserName\">Recipient</label>
                                <input type=\"text\" class=\"form-control\" name=\"UserName\" id=\"UserName$RecordID\" value=\"$UserData\" readonly>
                                <input type=\"hidden\" class=\"form-control\" name=\"Userid\" id=\"Userid$RecordID\" value=\"$UserID\">
                            </div>
                            <div class=\"form-group\">
                                <label for=\"UserPass\">Message<span class=\"required\">*</span></label>
                                <textarea class=\"form-control\" rows=\"3\" id=\"message$RecordID\" data-plugin-textarea-autosize placeholder='write message here' required></textarea>
                            </div>
                            
                            <div class=\"modal-footer\">
                                <button type=\"button\" class=\"btn btn-secondary\" data-bs-dismiss=\"modal\">Close</button>
                                <button type=\"button\" class=\"btn btn-primary\" name=\"Save\" id=\"Save\" value=\"Send\" 
                                onclick= \"
                                var toID = document.getElementById('Userid$RecordID').value;
                                var uMessage = document.getElementById('message$RecordID').value;

                                SendNotification('$LoggedUserID', toID, uMessage, '$DataCompanyID');
                                \">
                                Send Message
                                </button>
                             </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>";

     $SubData[] = $il;
     $SubData[] = $actions;
     $SubData[] = $RecordID;
     $SubData[] = $DivisionName;
     $SubData[] = $DistrictName;
     $SubData[] = $UserData;
     $SubData[] = $UserMobileNo;
     $SubData[] = $DataName;
     $SubData[] = $DataStatus;

     $il++;
     $data[] = $SubData;
 }

$jsonData = json_encode($data);

echo '{"aaData":' . $jsonData . '}';