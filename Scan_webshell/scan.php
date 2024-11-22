<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./public/css/style.css">
    <link rel="stylesheet" href="./public/css/bootstrap.css">
    <link rel="stylesheet" href="./public/css/bootstrap.min.css">
    <title>Scan</title>
    <script src="./public/js/script.js"></script>
    <script src="./public/js/chart.js"></script>
    <script src="./public/js/jquery-3.6.1.min.js"></script>
    <script src="./public/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container-xxl">
        <div class="row">
            <div class="col-sm-2">
                <ul class="list-group list-group-flush p-3">
                    <li class="list-group-item list-group-action"><a class="nav-link active" href="./index.php">Dashboard</a></li>
                    <li class="list-group-item list-group-action"><a class="nav-link" href="./scan.php">Quét</a></li>
                    <li class="list-group-item list-group-action"><a class="nav-link" href="./setting.php">Cài đặt</a></li>
                </ul>
            </div>
            <div class="col-sm-10 p-4">
                <div class="container d-flex justify-content-center align-items-center flex-wrap">
                    <div class="scan-req-box">
                        <h4 class="h4 font-weight-bold text-center">QUÉT SERVER</h4>
                        <form action="" method="post" class="scan-req-form">
                            <div class="form-group">
                                <label for="scan-location" id="" class="scan-location-label">Vị trí quét</label>
                                <input type="text" name="scan-location-input" class="scan-req-input form-control" id="scan-location" placeholder="./" value="<?php $folderLocation=$_POST["scan-location-input"] ?? ""; echo htmlspecialchars($folderLocation, ENT_QUOTES);?>"> <br>
                                <small id="" class="form-text text-muted">Mặc định sẽ là từ thư mục chứa module.</small>
                            </div>
                            <div class="form-group">
                                <input type="submit" value="Quét" name="btn" class="whi-color btn scan-req-btn bg-pri-color" id="scan-btn">
                                <input type="submit" value="Tìm vị trí" name="btn" class="whi-color btn scan-req-btn bg-pri-color">
                            </div>
                        </form>
                        <div class="progress invisible" id="progress-box">
                            <div class="progress-bar" id="scan-progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <?php
                        include("./class/clsScan.php");
                        $p = new clsScan();

                        if (isset($_POST["btn"]))
                        {
                            switch ($_POST["btn"])
                            {
                                case "Tìm vị trí": {
                                    $findFolderName = $_POST["scan-location-input"];
                                    if ($findFolderName == "")
                                    {
                                        echo "Tên folder không được để trống";
                                    } else {
                                        $filesInDir = $p->findFolderLocation($findFolderName);
                                        $count = 0;
                                        echo "
                                            <table class='table table-striped w-75' id='find-form-box'>
                                                    <thead>
                                                        <tr>
                                                        <th scope='col'>STT</th>
                                                        <th scope='col'>Đường dẫn</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                        ";

                                        foreach ($filesInDir as $filePath)
                                        {
                                            $count++;

                                            echo "
                                                <tr>
                                                    <th scope='row'>{$count}</th>
                                                    <td>{$filePath}</td>
                                                    </tr>
                                                <tr>
                                            ";
                                        }
                                        echo "</tbody></table>";
                                    }
                                                
                                    break;
                                }
                                case "Áp dụng": {
                                    $inputFile = $_POST["action-file-location"];
                                    $action = $_POST["action-file-chose"];
                                    $p->quaranFile($inputFile, $action);
                                    break;
                                }
                            }
                        }
                    ?>
                    <div id="result" class="scan-req-box"></div>
                </div>
            </div>
        </div>
    </div>
    <script>
        const btnScan = document.getElementById('scan-btn');
        const progressBox = document.getElementById('progress-box');
        const progressBar = document.getElementById('scan-progress-bar');
        const result = document.getElementById('result');
        
        btnScan.addEventListener("click", sendRequest);

        function updateProgress()
        {
            progressBox.classList.remove('invisible');
            fetch(`./progress.json?nocache=${new Date().getTime()}`, {
                    method: 'GET'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json(); // Parse JSON từ phản hồi
            })
            .then(data => {
                const percent = data[0]?.percent || 0;

                progressBar.setAttribute('aria-valuenow', percent);
                progressBar.style.width = percent + '%';
                progressBar.innerHTML = percent + '%';

                if (percent >= 100) {
                    getResultScan();
                }else {
                    setTimeout(updateProgress, 700);
                }
            })
            .catch(error => {
                console.error('Có lỗi xảy ra:', error);
            });
        }

        function getResultScan ()
        {
            const url = "./resultScan.php";
            const formData = new FormData();
            formData.append('btn', 'Lấy kết quả');

            fetch(url, {
                    method: 'POST',
                    body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json(); 
            })
            .then(data => {
                printResult(data);
            })
            .catch(error => {
                console.error('Có lỗi xảy ra:', error);
            });
        }

        function sendRequest (e)
        {
            if (document.getElementById('find-form-box'))
            {
                document.getElementById('find-form-box').classList.add("d-none");
            } 
            e.preventDefault();
            const url = "./resultScan.php";
            const scanLocation = document.getElementById('scan-location').value;
            const formData = new FormData();
            formData.append('scan-location-input', scanLocation);
            formData.append('btn', 'Quét');
            
            fetch(url, {
                method: 'POST',
                body: formData
            }).catch(() => {});
            updateProgress();
        }      

        function printResult(files)
        { 
            let resultHtml = `<form method='POST' class='form-group'>
                        <table class='table table-striped'>
                            <thead>
                                <tr>
                                <th scope='col'>STT</th>
                                <th scope='col'>Đường dẫn</th>
                                <th scope='col'>Kết quả</th>
                                </tr>
                            </thead>
                            <tbody>`;
            let numFile = files.length;

            if (numFile <= 0)
            {
                resultHtml += `<tr><td colspan="3" class="text-center">Không tìm thấy webshell</td></tr>`;
            } else {
                for (let i=0; i < numFile; i++)
                {
                    let number = i+1;
                    resultHtml += `<tr class='cursor_point' data-toggle='collapse' data-target='#details${number}' aria-expanded='false' aria-controls='details${number}'>
                                    <th scope='row'>${number}</th>
                                    <td>${files[i].filePath}</td>
                                    <td>${files[i].type}</td> 
                                </tr>
                                <tr class='collapse' id='details${number}'>
                                    <td colspan='3'>
                                        <div class='card p-3'>
                                            <div class='mb-2'><strong>Hash:</strong> ${files[i].SHA256Hash}</div>
                                            <div class='mb-2'><strong>Size:</strong> ${files[i].size}</div>
                                            <div class='mb-2'><strong>Signature:</strong> </div>
                                            <table class='table table-sm table-bordered'>
                                                <thead class='table-light'>
                                                    <tr>
                                                        <th scope='col'>Chữ ký</th>
                                                        <th scope='col'>Vị trí</th>
                                                    </tr>
                                                </thead>
                                                <tbody>`;
                    for (let j=0; j < files[i].signature.length; j++)
                    {
                        resultHtml += `<tr>
                            <td>${files[i].signature[j][0]}</td>
                            <td>${files[i].signature[j][1]}</td>
                        </tr>`;
                    }
    
                    resultHtml += `</tbody></table><div class='row'>
                                            <input name='action-file-location[]' value='${files[i].filePath}' hidden>
                                            <label for='action-file${number}' class='col-sm-5 col-form-label'>Hành động</label>
                                            <select id='action-file${number}' name='action-file-chose[]' class='cursor_point form-select col-sm-5'>
                                                <option value='1'>Cho phép</option>
                                                <option selected value='2'>Cách ly</option>
                                            </select>
                                        </div>
                                    </div>
                                </td>
                            </tr>`;
                }
                
                resultHtml += `<input type='submit' value='Áp dụng' name='btn' class='whi-color btn scan-req-btn bg-pri-color mb-3'>`;
            }
            
            const endHtml = `</tbody></table></form>`;
            result.innerHTML = resultHtml;
        }
    </script>
</body>
</html>