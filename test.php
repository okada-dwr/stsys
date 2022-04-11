<?php
require_once('tcpdf/tcpdf.php');
require_once('tcpdf/autoload.php');

$pdf = new setasign\Fpdi\Tcpdf\Fpdi();

$pdf->setPrintHeader(false);

$pdf->setSourceFile("./img/a.pdf");
$pdf->AddPage();
$tpl = $pdf->importPage(1);
$pdf->useTemplate($tpl);


$number = 11111111;
$name = 222222;
$price = 3333333333;
$proviso = 4444444444;

//$pdf->SetFont('kozminproregular', スタイル, サイズ);
//$pdf->Text(x座標, y座標, テキスト);

//No.
$pdf->SetFont('kozminproregular', '', 11);
$pdf->Text(150, 11, htmlspecialchars($number));

//名前
$pdf->SetFont('kozminproregular', '', 20);
$pdf->Text(15, 35, htmlspecialchars($name));

//金額
$pdf->SetFont('kozminproregular', '', 20);
$price = number_format($price) . "-";
$pdf->Text(70, 70, htmlspecialchars($price));

//但し書き
$pdf->SetFont('kozminproregular', '', 11);
$pdf->Text(70, 85, htmlspecialchars($proviso));

//日付
$pdf->SetFont('kozminproregular', '', 11);
$today = date("Y年m月d日");
$pdf->Text(150, 21, $today);

//$pdf->Output(出力時のファイル名, 出力モード);
$pdf->Output("output.pdf", "I");
