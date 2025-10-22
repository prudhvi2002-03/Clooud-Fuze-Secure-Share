<?php
$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

$password = $_POST['password'] ?? '';
$expiry = $_POST['expiry'] ?? '';
$files = $_FILES['files'] ?? null;

if(!$files || $password == '') {
    die("Missing data");
}

$token = bin2hex(random_bytes(8));
$tokenFile = $uploadDir.$token.'.json';

$uploadedFiles = [];
for($i=0; $i<count($files['name']); $i++){
    $tmp = $files['tmp_name'][$i];
    $name = basename($files['name'][$i]);
    $target = $uploadDir.$token.'_'.$name;
    if(move_uploaded_file($tmp, $target)){
        $uploadedFiles[] = $target;
    }
}

file_put_contents($tokenFile, json_encode([
    'files' => $uploadedFiles,
    'password' => password_hash($password, PASSWORD_DEFAULT),
    'expiry' => $expiry
]));

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$path = dirname($_SERVER['PHP_SELF']);
echo "$protocol://$host$path/download.php?token=$token";
?>
