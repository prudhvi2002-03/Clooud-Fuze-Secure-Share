<?php
$uploadDir = 'uploads/';
$token = $_GET['token'] ?? '';
$tokenFile = $uploadDir.$token.'.json';

if(!file_exists($tokenFile)) {
    die("<h2 style='text-align:center;color:red;'>Invalid or expired link!</h2>");
}

$data = json_decode(file_get_contents($tokenFile), true);

if($data['expiry'] && strtotime($data['expiry']) < time()){
    die("<h2 style='text-align:center;color:red;'>Link has expired!</h2>");
}

$error = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $entered = $_POST['password'] ?? '';
    if(password_verify($entered, $data['password'])){
        $zipName = $uploadDir.$token.'.zip';
        $zip = new ZipArchive();
        if($zip->open($zipName, ZipArchive::CREATE) === TRUE){
            foreach($data['files'] as $file){
                $zip->addFile($file, basename($file));
            }
            $zip->close();
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="files.zip"');
            readfile($zipName);
            exit;
        } else { $error = "Failed to create ZIP"; }
    } else { $error = "Incorrect password"; }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Secure File Download</title>
<style>
body { font-family:'Segoe UI'; background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); margin:0;display:flex;justify-content:center;align-items:center;height:100vh;}
.card { background: rgba(255,255,255,0.95); padding:30px;border-radius:20px;box-shadow:0 20px 40px rgba(0,0,0,0.1); text-align:center; width:90%; max-width:400px;}
.card h2 { background: linear-gradient(135deg,#667eea,#764ba2); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; margin-bottom:20px;}
input[type="password"] { width:100%; padding:12px 16px; border-radius:12px; border:2px solid #e2e8f0; margin-bottom:15px; font-size:15px; }
input:focus { outline:none; border-color:#667eea; box-shadow:0 0 0 3px rgba(102,126,234,0.1); }
button { background: linear-gradient(135deg, #48bb78,#38a169); color:white; border:none; padding:12px 25px; border-radius:12px; font-size:16px; font-weight:600; cursor:pointer; transition:0.3s;}
button:hover { box-shadow:0 8px 25px rgba(72,187,120,0.4); transform:translateY(-2px);}
.error { color:red; margin-bottom:15px;}
</style>
</head>
<body>
<div class="card">
    <h2>🔒 Secure File Download</h2>
    <?php if($error) echo "<div class='error'>$error</div>"; ?>
    <form method="post">
        <input type="password" name="password" placeholder="Enter Password" required>
        <button type="submit">Download Files</button>
    </form>
</div>
</body>
</html>
