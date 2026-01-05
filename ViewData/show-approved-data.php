<?php
$qrySupervisor = "SELECT id FROM assignsupervisor WHERE SupervisorID = ?";
$rsSupervisor = $app->getDBConnection()->fetch($qrySupervisor, $loggedUserID);
$SuperID = $rsSupervisor->id;

if (strpos($loggedUserName, 'dist') !== false) {
    $divQuery = "SELECT DISTINCT p.DivisionName, p.DivisionCode FROM PSUList AS p 
    JOIN assignsupervisor AS a ON p.PSUUserID = a.UserID 
    WHERE  p.CompanyID = ? AND a.DistCoordinatorID = ?";
    $rsDivQuery = $app->getDBConnection()->fetchAll($divQuery, $loggedUserCompanyID, $loggedUserID);
} elseif (strpos($loggedUserName, 'cs') !== false) {
    $divQuery = "SELECT DISTINCT p.DivisionName, p.DivisionCode FROM PSUList AS p 
    JOIN assignsupervisor AS a ON p.PSUUserID = a.UserID 
    WHERE  p.CompanyID = ? AND a.SupervisorID = ?";
    $rsDivQuery = $app->getDBConnection()->fetchAll($divQuery, $loggedUserCompanyID, $loggedUserID);
} elseif (strpos($loggedUserName, 'val') !== false) {
    $divQuery = "SELECT DISTINCT p.DivisionName, p.DivisionCode FROM PSUList AS p 
    JOIN assignsupervisor AS a ON p.PSUUserID = a.UserID 
    WHERE  p.CompanyID = ? AND a.ValidatorID = ?";
    $rsDivQuery = $app->getDBConnection()->fetchAll($divQuery, $loggedUserCompanyID, $loggedUserID);
} else {
    $divQuery = "SELECT DISTINCT DivisionName , DivisionCode FROM PSUList WHERE CompanyID = ? ORDER BY DivisionName ASC";
    $rsDivQuery = $app->getDBConnection()->fetchAll($divQuery, $loggedUserCompanyID);
}

if (strpos($loggedUserName, 'cval') !== false) {
    $divQuery = "SELECT DISTINCT p.DivisionName, p.DivisionCode FROM PSUList AS p 
    JOIN assignsupervisor AS a ON p.PSUUserID = a.UserID 
    WHERE  p.CompanyID = ?";
    $rsDivQuery = $app->getDBConnection()->fetchAll($divQuery, $loggedUserCompanyID);
}

if ($_REQUEST['show'] === 'Show') {

    $SelectedFormID = xss_clean($_REQUEST['SelectedFormID']);

    $DivisionCode = xss_clean($_REQUEST['DivisionCode']);
    $DistrictCode = xss_clean($_REQUEST['DistrictCode']);
    $UpazilaCode = xss_clean($_REQUEST['UpazilaCode']);
    $UnionWardCode = xss_clean($_REQUEST['UnionWardCode']);
    $MauzaCode = xss_clean($_REQUEST['MauzaCode']);
    $VillageCode = xss_clean($_REQUEST['VillageCode']);

    $SelectedUserID = xss_clean($_REQUEST['SelectedUserID']);
    $SelectedStartDate = xss_clean($_REQUEST['startDate']);
    $SelectedEndDate = xss_clean($_REQUEST['endDate']);
    $checkAll = xss_clean($_REQUEST['chkAll']);
}
?>

<script src="https://cdn.jsdelivr.net/gh/StephanWagner/jBox@v1.3.2/dist/jBox.all.min.js"></script>
<link href="https://cdn.jsdelivr.net/gh/StephanWagner/jBox@v1.3.2/dist/jBox.all.min.css" rel="stylesheet">

<div class="inner-wrapper">
    <section role="main" class="content-body">
        <header class="page-header">
            <h2><?php echo $MenuLebel; ?></h2>

            <?php include_once 'Components/header-home-button.php'; ?>
        </header>

        <!-- start: page -->
        <div class="row">
            <div class="col-lg-12 mb-0">
                <section class="card">
                    <div class="card-body">
                        <form class="form-horizontal form-bordered" action="" method="post">
                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">Form Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo id="SelectedFormID" name="SelectedFormID"
                                            class="form-control populate" required>
                                        <optgroup label="Choose form">
                                            <?PHP
                                            $qryForm = $app->getDBConnection()->query("SELECT id, FormName FROM datacollectionform WHERE Status = 'Active' AND CompanyID = ?", $loggedUserCompanyID);

                                            foreach ($qryForm as $row) {
                                                echo '<option value="' . $row->id . '"' . (!empty($SelectedFormID) && $row->id == $SelectedFormID ? ' selected' : '') . '>' . $row->FormName . '</option>';
                                            }
                                            ?>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>
                            <?php
                            if (strpos($loggedUserName, 'cs') === false) {
                                ?>
                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-sm-end pt-2">Division Select</label>
                                    <div class="col-lg-6">
                                        <select data-plugin-selectTwo class="form-control populate" name="DivisionCode"
                                                id="DivisionCode"
                                                onchange="ShowDropDown4('DivisionCode', 'DistrictDiv','userDiv', 'DistrictUser', ['DivisionCode'], {'RequiredUser':0})">
                                            <option value="">Choose division</option>
                                            <?PHP
                                            foreach ($rsDivQuery as $row) {
                                                echo '<option value="' . $row->DivisionCode . '"' . (!empty($DivisionCode) && $row->DivisionCode == $DivisionCode ? ' selected' : '') . '>' . $row->DivisionName . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div id="geoDiv" style="display: none">
                                    <div class="form-group row pb-3" id="DistrictDiv"></div>
                                </div>
                                <?php
                            }
                            ?>

                            <?php
                            if (strpos($loggedUserName, 'admin')) {
                                ?>
                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-sm-end pt-2"></label>
                                    <div class="col-lg-6">
                                        <div class="checkbox-custom checkbox-warning">
                                            <input id="chkAll" value="chkAll" type="checkbox"
                                                   name="chkAll" <?php echo isset($checkAll) && $checkAll == 'chkAll' ? 'checked' : ''; ?> />
                                            <label for="chkAll">All Records</label>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>

                            <footer class="card-footer">
                                <div class="row justify-content-end">
                                    <div class="col-lg-9">
                                        <input class="btn btn-primary" name="show" type="submit" id="show" value="Show">
                                        <button type="button" class="btn btn-secondary ms-4" id="clearForm">Clear
                                        </button>
                                    </div>
                                </div>
                            </footer>
                        </form>
                    </div>
                </section>
                <?php
                if ($_REQUEST['show'] === 'Show') {
                    $SelectedFormID = $_REQUEST['SelectedFormID'];
                    $SelectedCompanyID = getValue('datacollectionform', 'CompanyID', "id = $SelectedFormID");
                    $SelectedDiv = $_REQUEST['DivisionCode'];
                    $SelectedDist = $_REQUEST['DistrictCode'];
                    $checkAll = $_REQUEST['chkAll'];

                    $DataStatusCode = $DataStatusApproved;

                    if (empty($SelectedDiv) && empty($SelectedDist) && empty($checkAll)) {
                        MsgBox('Please select an option.');
                        ReloadPage();
                    } else {
                        if ($checkAll == 'chkAll') {
                            $dataURL = $baseURL . "ViewData/ajax-data/view-approved-data-ajax-data.php?statusCode=$DataStatusCode&chkAll=1&frmID=$SelectedFormID&lun=$loggedUserName&lci=$loggedUserCompanyID&luid=$loggedUserID";
                        } else {
                            $dataURL = $baseURL . "ViewData/ajax-data/view-approved-data-ajax-data.php?statusCode=$DataStatusCode&frmID=$SelectedFormID&lun=$loggedUserName&lci=$loggedUserCompanyID&luid=$loggedUserID&divCode=$SelectedDiv&distCode=$SelectedDist";
                        }

                        ?>

                        <section class="card">
                            <header class="card-header">
                                <div class="form-group ml-2 row col-lg-1 " style="margin-left: 1px; margin-top:20px;">
                                    <button class="btn ml-2 btn-success"
                                            onclick="exportTableToExcel('datatable-ajax', 'UserList')">
                                        Download
                                    </button>
                                </div>
                            </header>
                            <div class="card-body">
                                <table class="table table-bordered table-striped" id="datatable-ajax"
                                       data-url="<?php echo $dataURL; ?>">
                                    <thead>
                                    <tr>
                                        <th>SL</th>
                                        <th>Actions</th>
                                        <th>Record ID</th>
                                        <th>Division Name</th>
                                        <th>District Name</th>
                                        <th>User</th>
                                        <th>Mobile</th>
                                        <th>Data Name</th>
                                        <th>Status</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    function SendNotification(senderID, toID, message, companyID, data) {
        if (confirm("Are you sure to send this message?")) {
            $.ajax({
                url: "ViewData/send-notification.php",
                method: "GET",
                datatype: "json",
                data: {
                    senderID: senderID,
                    toID: toID,
                    message: message,
                    companyID: companyID
                },
                success: function(response) {
                    alert(response);
                    window.location.reload();
                }
            });
        }
        return false;
    }
</script>

<script type="text/javascript">
    function CheckDataRecord(id, sendTo, loggedUserID, data) {
        if (confirm("Are you sure to mark as Checked this data?")) {
            $.ajax({
                url: "ViewData/check-data.php",
                method: "GET",
                datatype: "json",
                data: {
                    id: id,
                    tbl: 'xformrecord',
					loggedUserID: loggedUserID
                },
                success: function(response) {
                    alert(response);
                    window.location.reload();
                }
            });
        }
        return false;
    }
</script>

<script type="text/javascript">
    function DeleteDataRecord(id, sendTo, data) {
        let cause = prompt("Are you sure to delete this data?", "Cause of delete: ")
        if (cause) {
            $.ajax({
                url: "ViewData/delete-data.php",
                method: "GET",
                datatype: "json",
                data: {
                    id: id,
                    tbl: 'xformrecord',
                    SendTo: sendTo,
                    cause: cause,
                    FromState: 'Approved',
                    sendFrom: '<?php echo $loggedUserID; ?>',
                    companyID: '<?php echo $loggedUserCompanyID; ?>',
                },
                success: function(response) {
                    alert(response);
                    window.location.reload();
                }
            });
        }
        return false;
    }
</script>

<script type="text/javascript">
    function UnapproveDataRecord(id, sendTo, data) {
        let cause = prompt("Are you sure to un-approve this data?", "Cause of un-approve: ");
		var CommentsFields = JSON.stringify($('#CommentsFields').serializeArray());
        if (cause) {
            $.ajax({
                url: "ViewData/unapprove-data.php",
                method: "POST",
                datatype: "json",
                data: {
                    id: id,
                    tbl: 'xformrecord',
                    SendTo: sendTo,
                    cause: cause,
					CommentsFields: CommentsFields,
                    FromState: 'Approved',
                    sendFrom: '<?php echo $loggedUserID; ?>',
                    companyID: '<?php echo $loggedUserCompanyID; ?>',
                },
                success: function(response) {
                    alert(response);
                    window.location.reload();
                }
            });
        }
        return false;
    }
</script>

<script>
    $(document).ready(function() {
        // Initial population on page load
        populateDropdowns(
            <?php echo isset($DivisionCode) && $DivisionCode !== '' ? $DivisionCode : 'null'; ?>,
            <?php echo isset($DistrictCode) && $DistrictCode !== '' ? $DistrictCode : 'null'; ?>,
            <?php echo isset($UpazilaCode) && $UpazilaCode !== '' ? $UpazilaCode : 'null'; ?>,
            <?php echo isset($UnionWardCode) && $UnionWardCode !== '' ? $UnionWardCode : 'null'; ?>,
            <?php echo isset($MauzaCode) && $MauzaCode !== '' ? $MauzaCode : 'null'; ?>,
            <?php echo isset($VillageCode) && $VillageCode !== '' ? $VillageCode : 'null'; ?>
        );
    });
</script>