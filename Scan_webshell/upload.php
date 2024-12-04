<?php     
    require_once('./class/clsUpload.php');
    $upload = new clsUpload();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./public/css/bootstrap.css">
    <link rel="stylesheet" href="./public/css/bootstrap.min.css">
    <link rel="stylesheet" href="./public/css/style.css">
    <link rel="stylesheet" href="./public/css/dataTables.bootstrap5.min.css">
    <title>Tải lên</title>
    <script src="./public/js/chart.js"></script>
    <script src="./public/js/jquery-3.6.4.min.js"></script>
    <script src="./public/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-2">
                <ul class="list-group list-group-flush p-3">
                    <li class="list-group-item list-group-action sidebar-menu"><a class="nav-link" href="./index.php">Dashboard</a></li>
                    <li class="list-group-item list-group-action sidebar-menu"><a class="nav-link active" href="./upload.php">Tải lên</a></li>
                    <li class="list-group-item list-group-action sidebar-menu"><a class="nav-link" href="./scan.php">Quét</a></li>
                    <li class="list-group-item list-group-action sidebar-menu"><a class="nav-link" href="./setting.php">Cài đặt</a></li>
                </ul>
            </div>
            <div class="col-sm-10 p-4">
                <div class="container d-flex justify-content-center align-items-center flex-wrap">
                    <div class="scan-req-box">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="fileList-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true">Danh sách tệp</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="upload-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="false">Tải tệp lên</button>
                            </li>
                        </ul>
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="fileList-tab">
                                <div class="mt-4">
                                    <?php 
                                        $upload->showUploadFiles();
                                    ?>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="upload-tab">
                                <?php
                                    $upload->showFormUpload();
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="./public/js/bootstrap.bundle.min.js"></script>
    <script src="./public/js/jquery-3.6.4.min.js"></script>
    <script src="./public/js/jquery.dataTables.min.js"></script>
    <script src="./public/js/dataTables.bootstrap5.min.js"></script>
    <script>
    // Kích hoạt DataTables
    $(document).ready(function () {
        $('#fileTable').DataTable({
            "language": {
                "lengthMenu": "Hiển thị _MENU_ mục",
                "search": "Tìm kiếm:",
                "info": "Hiển thị _START_ đến _END_ của _TOTAL_ mục",
                "paginate": {
                    "previous": "<",
                    "next": ">"
                },
                emptyTable: "Không có dữ liệu để hiển thị"
            }
        });
    });
</script>
</body>
</html>