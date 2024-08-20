

<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload File</title>
</head>
<body>  
    <form action="/" method="post" enctype="multipart/form-data">
        <label for="file">Виберіть файл для завантаження:</label>
        <input type="file" id="file" name="file" required>
        <input type="submit" value="Upload File">
    </form>
</body>
</html>

<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $uploadDirectory = 'uploads/';
    $uploadFile = $uploadDirectory . basename($_FILES['file']['name']);

    // Создаем директорию, если её нет
    if (!is_dir($uploadDirectory)) {
        mkdir($uploadDirectory, 0777, true);
    }

    // Удаляем все файлы в директории
    $files = glob($uploadDirectory . '*'); // Получаем все файлы в директории
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file); // Удаляем файл
        }
    }

    // Проверка на ошибки
    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        echo "Ошибка при загрузке файла.";
        exit;
    }

    // Перемещаем загруженный файл в указанную директорию
    if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
        echo "Файл успешно загружен.";
    } else {
        echo "Ошибка при перемещении загруженного файла.";
    }
} else {
    echo "Неверный метод запроса.";
}




// ini_set('error_reporting', E_ALL);
// ini_set('display_errors', 1);
ini_set('max_execution_time', 0);
require 'vendor/autoload.php';

function format ($expre) {
    echo "<pre>";
    print_r($expre);
    echo "</pre>";
  }
  //форматування цін
  function format_price ($number){
    $cleanStr = str_replace('.', '', $number);
    $cleanStr = explode(',', $cleanStr)[0];
    $cleanNumber = (int)$cleanStr;
    $number_with_20_percent = $cleanNumber * 1.20;

// Добавляем 5% к результату
$result = $number_with_20_percent * 1.10;
return $result;

  } 

  $files_read = glob($uploadDirectory . '*'); // Получаем все файлы в директории


use Smalot\PdfParser\Parser;
$parser = new Parser();
$pdf  = $parser->parseFile($files_read[0]);
$text = $pdf->getText();

preg_match_all('/\d+\.\d+-\d+\.\d+/', $text, $sku);
// format($sku[0]);

$qnty_prod = preg_match_all('/ШТ\s*(\d+)/', $text, $gnt_prd);
// format($gnt_prd[1]);

preg_match_all('/ШТ\s*\d+\s+([\d,.]+)/', $text, $price_prd);

// format($price_prd[1]);

preg_match_all('/\.\d+(-)(.*?)\s+ШТ/', $text, $name_prd);

preg_match('/Видаткова накладна\s+(.*?)\s+№/', $text, $document_number);



$doc_name = $document_number[1];

function format_product_name($string) {
    $newString = preg_replace("/\.\d{3}-\d{3}\.\d/", "", $string);
    $newString = str_replace("ШТ", "", $newString);
    return $newString;
    

}
// format($name_prd[0]);
$formated_product_arr = array_map('format_product_name', $name_prd[0]);
// format($formated_product_arr);


$combinedArray = [];

$formated_price_arr = [];

$formated_price_arr = array_map('format_price', $price_prd[1]);
// format($formated_price_arr);


for ($i = 0; $i < count($sku[0]); $i++) {
    $combinedArray[] = [
        $sku[0][$i],
        $gnt_prd[1][$i],
        $formated_product_arr[$i],
        $formated_price_arr[$i],
    ];
}

// format($combinedArray);

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
foreach ($combinedArray as $rowIndex => $row) {
    foreach ($row as $colIndex => $value) {
        $sheet->setCellValueByColumnAndRow($colIndex + 1, $rowIndex + 1, $value);
    }
}
$writer = new Xlsx($spreadsheet);
$writer->save($doc_name.'.xlsx');
$fileUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $doc_name.'.xlsx';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Download Spreadsheet</title>
</head>
<body>
    <h1>Ваш файл готовий до скачування у форматі xls</h1>
    <a href="<?php echo $fileUrl; ?>">Завантажити файл</a>
</body>
</html>