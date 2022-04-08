<?php
echo "a";
include("./TCPDF/TCPDF-main/tcpdf.php");

define("MY_PDF_PAGE_ORIENTATION", "P");  // P:Portrait, L:Landscape
define("MY_PDF_FONT_NAME", "msmincho");  // kozminproregular
define("MY_PDF_FONT_SIZE", 10);
define("MY_PDF_UNIT", "mm");
define("MY_PDF_PAGE_FORMAT", "A4");
define("MY_PDF_IMAGE_SCALE_RATIO", 1);
define("MY_PDF_MARGIN_HEADER", 0);
define("MY_PDF_MARGIN_FOOTER", 0);
define("MY_PDF_MARGIN_TOP", 10);
define("MY_PDF_MARGIN_LEFT", 8); //余白
define("MY_PDF_MARGIN_RIGHT", 8); //余白
define("MY_PDF_MARGIN_BOTTOM", 13); //余白

echo "b";

// class MYPDF extends TCPDF
// {

//     // フッタのカスタマイズ(ページ番号を出力する)
//     public function Footer()
//     {
//         // $this->SetY(-15);  // Position at 15 mm from bottom
//         // $this->SetFont('helvetica', 'I', 8);
//         // $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
//     }
// }

echo "c";
$pdf = new TCPDF(MY_PDF_PAGE_ORIENTATION, MY_PDF_UNIT, MY_PDF_PAGE_FORMAT, true, 'UTF-8', false);
echo "d";
$pdf->SetTitle('PDF出力テスト');
//$pdf->SetSubject('TCPDF Tutorial');
//$pdf->SetHeaderData(null, null, null, null);
//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 006', PDF_HEADER_STRING);
//$pdf->setHeaderFont(Array(MY_PDF_FONT_NAME, '', MY_PDF_FONT_SIZE));
//$pdf->setFooterFont(Array(MY_PDF_FONT_NAME, '', MY_PDF_FONT_SIZE));
//$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(MY_PDF_MARGIN_LEFT, MY_PDF_MARGIN_TOP, MY_PDF_MARGIN_RIGHT); //余白
//$pdf->SetHeaderMargin(MY_PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(MY_PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, MY_PDF_MARGIN_BOTTOM);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(true);
$pdf->setImageScale(MY_PDF_IMAGE_SCALE_RATIO);
$pdf->SetFont(MY_PDF_FONT_NAME, "", 10);

echo "d";
// ページを追加
$pdf->AddPage();

echo "e";
// PDFに変換するHTML
$html = <<<_EO_HTML_
<style>
.f10px {
    font-size: 10px;}

.f11px {
    font-size: 11px;}

.f14px {
    font-size: 14px;}

.under {
    text-decoration: underline;}

.center {
    text-align: center;}

.right {
    text-align: right;}

.font_weight1{
    font_weight: bold;}

</style>

<p class="f11px right">年　月　日</p>
<p class="f14px right font_weight1">志摩機械株式会社
<br><span class="f11px right">北近畿教習センター</span></p>
<p class="f14px under center">小型移動式クレーン運転技能講習のご案内（K1コース）</p>
<p class="f11px">  平素は格別のお引立てを賜り厚く御礼申し上げます。
<br>さて､先日ご予約戴きました小型移動式クレーン運転技能講習を下記要領にて実施することになりましたので
<br>ご案内申し上げます。<br>&nbsp;&nbsp;尚､ご都合により参加できなくなった場合には至急電話などで必ずご連絡くださいます様お願い申し上げます。</p>
<p class="f11px center">― 　記　 ―</p>
<p class="f11px M_item">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1. 日&nbsp;&nbsp;&nbsp;&nbsp;程&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2021年12月 17日 （ 金　 ） ～　12 月　19 日（ 日  ）</p>
<p class="f11px M_item">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2. 受付開始&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;8:00より（時間厳守）
<p class="f11px M_item">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3. 講習時間&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;初 日  8:30～18:10（学科）
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2日目　8:30～17：40（学科・実技)
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3日目　8:15～16：30（実技）</p>
<p class="f11px M_item">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;4. 場&nbsp;&nbsp;&nbsp;&nbsp;所&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;〒624-0951    京都府舞鶴市字上福井117番地
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;志摩機械株式会社　北近畿教習センター
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;TEL 0773 - 75 - 0652   FAX 0773 - 76 - 5591</p>
<p class="f11px M_item">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;5. 受&nbsp;講&nbsp;料&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;35,000円（受講料及びテキスト代消費税込み）が未納の方は、至急下記の口座まで
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;お振込み下さい。（当日現金にて持参可）</p>

<div>
<table border="1" width="250px" cellpadding="3">
<tr><td class="f11px">
&nbsp;&nbsp;&nbsp;&nbsp;･  京都銀行   西舞鶴支店   普通預金
<br>&nbsp;&nbsp;&nbsp; ･  口座番号  　３０３４３５７　　
<br>&nbsp;&nbsp;&nbsp; ･  志摩機械株式会社
</td></tr></table>

<p class="f11px M_item">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;6. 持参して&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(1)  本人確認書類（氏名、生年月日、現住所の確認できる公的な書類）
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;いただく物&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;例（車の免許証、住民票等）
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(2)  印鑑・筆記用具（鉛筆、シャープペンシル、消しゴム）
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(3)  実技に適した服装、安全靴（運動靴でも可）、ヘルメット、カッパ
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(4)  申込書用・修了証用写真計1枚（証明写真3.0×2.4cm）
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;※無背景・無帽・サングラス不可、　カラーコピー・デジタルカメラ印刷不可
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(5)　当センター交付の技能講習修了証(H18.6以降に交付した修了証をお持ちの方のみ)</p>
<p class="f11px M_item">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;7. 昼    食&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;各自でご用意下さい。</p>
<table border="1">
<tr>
<td class="f14px">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;受講申込書は受講日までに必ず郵送又はＦＡＸにて提出願います。
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ＦＡＸにて提出された場合は申込書原本は当日ご持参下さい。
</td>
</tr>
</table>
<p class="f10px">※重要：新型コロナウイルス感染予防と拡大防止の対応で、予定しています講習が、中止となる場合があります。
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ご了承ください。
<br>ご受講される皆様におきましては、下記の点ご留意いただきますようお願いいたします。
<br> •ご来所時はマスク着用、手洗い、うがいの励行にご協力お願いいたします。
<br> •マスクはご自身でご用意くださいますようお願いいたします。
<br> •咳、発熱等の症状がある方は、受講の延期をご検討ください。講習中も状況により、ご受講をお控えいただく場合がございます。
<br>  また、受講中に体調が悪い方は検温させていただく場合がございます。
<br> •中止、延期の場合には教習所よりご連絡させていただきます。</p>
_EO_HTML_;

$pdf->writeHTML($html, true, false, true, false, '');

// 2ページ目追加
//$pdf->lastPage();
//$pdf->AddPage();
//$pdf->writeHTML($html, true, false, true, false, '');

/*
 * Output の第2引数にI,D,FI,FD を指定すればHTTPレスポンスヘッダ(Content-TypeやContent-Disposition)を自動的に出力してくれる。
 * 自分でヘッダを調整したい場合は、S でデータだけ取得して自分でヘッダを吐く。
 * (ファイル名に日本語を含めたい場合など.)
 */
$data = null;
$fileName = "テスト.pdf";
//$pdf->Output($fileName, 'I');     // ブラウザに表示
//$pdf->Output($fileName, 'D');     // ダウンロードダイアログを表示
//$pdf->Output($fileName, 'F');     // サーバにファイルを保存
$data = $pdf->Output(null, 'S');     // PDFドキュメントを文字列として返却
//$pdf->Output($fileName, 'FI');    // ファイルに保存して、ブラウザにも表示
//$pdf->Output($fileName, 'FD');    // ファイルに保存して、ダウンロードダイアログを表示
//$data = $pdf->Output(null, 'E');  // Base64エンコード済みのPDFドキュメントを返却(メールに添付するmultipartコンテンツ用なのでContent-Typeなどのヘッダーが付く)
echo "i";
if ($data != null) {
    // ブラウザにそのまま表示
    echo "H";
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . basename($fileName) . '"');
    // ダウンロード
    //header('Content-Type: application/octet-stream', false);
    //header('Content-Disposition: attachment; filename="'.basename($fileName).'"');
    echo $data;
}
