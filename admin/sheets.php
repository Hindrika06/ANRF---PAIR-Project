<?php
require_once 'auth_check.php';
require_once 'role_access.php';

$prefix = resolveAdminPrefix($_GET['prefix'] ?? null);

if (!isValidPrefix($prefix)) {
    die('Invalid institute configuration. Please contact admin.');
}
?>

<?php include 'nav_header.php'; ?>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
<?php include 'loader.php'; ?>

<div id="main-wrapper">
    <div class="content-body default-height">
        <div class="container-fluid">

            <?php include 'institute_banner.php'; ?>

            <div class="row page-titles mx-0">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0)">KPI</a>
                    </li>
                    <li class="breadcrumb-item active">
                        <a href="javascript:void(0)">Sheets</a>
                    </li>
                </ol>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Google Sheets Data</h4>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="sheetTable">
                            <thead></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="copyright">
            <p>
                Copyright &copy; Designed &amp; Developed by
                <a href="https://bhimavaramdigitals.com/" target="_blank">
                    Bhimavaram Digitals
                </a>
                2026
            </p>
        </div>
    </div>
</div>

<script src="vendor/global/global.min.js"></script>
<script src="vendor/bootstrap-select/js/bootstrap-select.min.js"></script>
<script src="js/custom.min.js"></script>
<script src="js/dlabnav-init.js"></script>

<script>
    function loadSheetData() {

        fetch('get_sheet_data.php?' + new Date().getTime())
            .then(response => response.json())
            .then(data => {

                const thead = document.querySelector("#sheetTable thead");
                const tbody = document.querySelector("#sheetTable tbody");

                thead.innerHTML = "";
                tbody.innerHTML = "";

                if (!data || data.length === 0) {
                    tbody.innerHTML =
                        '<tr><td colspan="20" class="text-center">No Data Found</td></tr>';
                    return;
                }

                // Header
                let header = "<tr>";

                data[0].forEach(col => {
                    header += `<th>${col}</th>`;
                });

                header += "</tr>";
                thead.innerHTML = header;

                // Data Rows
                for (let i = 1; i < data.length; i++) {

                    let row = "<tr>";

                    data[i].forEach(cell => {
                        row += `<td>${cell ?? ""}</td>`;
                    });

                    row += "</tr>";

                    tbody.innerHTML += row;
                }

            })
            .catch(error => {
                console.error("Google Sheets Error:", error);
            });
    }

    // Load immediately
    loadSheetData();

    // Auto refresh every 5 seconds
    setInterval(loadSheetData, 2000);
</script>

<?php include 'footer.php'; ?>