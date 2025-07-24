<?php
// ========================================
// TEMPLATE EXPORT EXCEL HTML
// File: application/views/staf/bimbingan/excel_export.php
// ========================================
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:x="urn:schemas-microsoft-com:office:excel"
xmlns="http://www.w3.org/TR/REC-html40">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="ProgId" content="Excel.Sheet">
<meta name="Generator" content="Microsoft Excel 15">
<title>Data Bimbingan Mahasiswa - STK Santo Yakobus</title>

<!--[if gte mso 9]><xml>
<x:ExcelWorkbook>
<x:ExcelWorksheets>
<x:ExcelWorksheet>
<x:Name>Data Bimbingan</x:Name>
<x:WorksheetOptions>
<x:DefaultRowHeight>260</x:DefaultRowHeight>
</x:WorksheetOptions>
</x:ExcelWorksheet>
</x:ExcelWorksheets>
</x:ExcelWorkbook>
</xml><![endif]-->

<style>
<!--table
	{mso-displayed-decimal-separator:"\.";
	mso-displayed-thousand-separator:"\,";}
@page
	{margin:.98in .79in .98in .79in;
	mso-header-margin:.51in;
	mso-footer-margin:.51in;}
tr
	{mso-height-source:auto;}
col
	{mso-width-source:auto;}
br
	{mso-data-placement:same-cell;}
.style0
	{mso-number-format:General;
	text-align:general;
	vertical-align:bottom;
	white-space:nowrap;
	mso-rotate:0;
	mso-background-source:auto;
	mso-pattern:auto;
	color:black;
	font-size:11.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:0;
	border:none;
	mso-protection:locked visible;
	mso-style-name:Normal;
	mso-style-id:0;}
.xl65
	{mso-style-parent:style0;
	font-weight:700;
	background:#4472C4;
	color:white;
	border:.5pt solid windowtext;}
.xl66
	{mso-style-parent:style0;
	border:.5pt solid windowtext;}
.xl67
	{mso-style-parent:style0;
	mso-number-format:"Short Date";
	border:.5pt solid windowtext;}
-->
</style>
</head>

<body>
<table border="0" cellpadding="0" cellspacing="0" width="1200" style="border-collapse: collapse;table-layout:fixed;width:900pt">

<!-- Header -->
<tr height="40" style="height:30.0pt">
<td colspan="11" height="40" class="xl65" style="height:30.0pt;text-align:center;font-size:16pt;">
DATA BIMBINGAN MAHASISWA TUGAS AKHIR<br>
STK SANTO YAKOBUS MERAUKE
</td>
</tr>

<tr height="20" style="height:15.0pt">
<td colspan="11" height="20" style="height:15.0pt;font-size:10pt;text-align:center;">
Digenerate oleh: <?= $generated_by ?> | Tanggal: <?= $generated_at ?>
</td>
</tr>

<tr height="20" style="height:15.0pt">
<td colspan="11" height="20" style="height:15.0pt;">&nbsp;</td>
</tr>

<!-- Table Header -->
<tr height="25" style="height:18.75pt">
<td height="25" class="xl65" width="40" style="height:18.75pt;width:30pt;">No</td>
<td class="xl65" width="100" style="width:75pt;">NIM</td>
<td class="xl65" width="200" style="width:150pt;">Nama Mahasiswa</td>
<td class="xl65" width="150" style="width:112pt;">Program Studi</td>
<td class="xl65" width="300" style="width:225pt;">Judul Proposal</td>
<td class="xl65" width="150" style="width:112pt;">Dosen Pembimbing</td>
<td class="xl65" width="150" style="width:112pt;">Email Pembimbing</td>
<td class="xl65" width="120" style="width:90pt;">Status Workflow</td>
<td class="xl65" width="100" style="width:75pt;">Tanggal Pengajuan</td>
<td class="xl65" width="150" style="width:112pt;">Email Mahasiswa</td>
<td class="xl65" width="120" style="width:90pt;">No. Telepon</td>
</tr>

<!-- Data Rows -->
<?php if (!empty($mahasiswa_data)): ?>
    <?php foreach ($mahasiswa_data as $index => $mhs): ?>
        <tr height="20" style="height:15.0pt">
            <td height="20" class="xl66" align="center" style="height:15.0pt;"><?= $index + 1 ?></td>
            <td class="xl66"><?= htmlspecialchars($mhs->nim) ?></td>
            <td class="xl66"><?= htmlspecialchars($mhs->nama_mahasiswa) ?></td>
            <td class="xl66"><?= htmlspecialchars($mhs->nama_prodi) ?></td>
            <td class="xl66"><?= htmlspecialchars($mhs->judul) ?></td>
            <td class="xl66"><?= htmlspecialchars($mhs->nama_pembimbing ?: 'Belum ditetapkan') ?></td>
            <td class="xl66"><?= htmlspecialchars($mhs->email_pembimbing ?: '-') ?></td>
            <td class="xl66"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $mhs->workflow_status))) ?></td>
            <td class="xl67"><?= date('d/m/Y', strtotime($mhs->tanggal_pengajuan)) ?></td>
            <td class="xl66"><?= htmlspecialchars($mhs->email_mahasiswa) ?></td>
            <td class="xl66"><?= htmlspecialchars($mhs->nomor_telepon ?: '-') ?></td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr height="20" style="height:15.0pt">
        <td colspan="11" height="20" class="xl66" style="height:15.0pt;text-align:center;">
            Tidak ada data mahasiswa bimbingan
        </td>
    </tr>
<?php endif; ?>

<!-- Footer Summary -->
<tr height="20" style="height:15.0pt">
<td colspan="11" height="20" style="height:15.0pt;">&nbsp;</td>
</tr>

<tr height="20" style="height:15.0pt">
<td colspan="2" height="20" class="xl65" style="height:15.0pt;">Total Mahasiswa:</td>
<td class="xl66" style="font-weight:bold;"><?= count($mahasiswa_data) ?></td>
<td colspan="8" class="xl66">&nbsp;</td>
</tr>

<tr height="20" style="height:15.0pt">
<td colspan="11" height="20" style="height:15.0pt;">&nbsp;</td>
</tr>

<!-- Summary by Status -->
<tr height="20" style="height:15.0pt">
<td colspan="11" height="20" class="xl65" style="height:15.0pt;text-align:center;">
RINGKASAN BERDASARKAN STATUS WORKFLOW
</td>
</tr>

<?php
// Hitung summary berdasarkan status
$status_summary = [];
foreach ($mahasiswa_data as $mhs) {
    $status = $mhs->workflow_status;
    if (!isset($status_summary[$status])) {
        $status_summary[$status] = 0;
    }
    $status_summary[$status]++;
}

$status_labels = [
    'bimbingan' => 'Tahap Bimbingan',
    'seminar_proposal' => 'Seminar Proposal',
    'penelitian' => 'Tahap Penelitian',
    'seminar_skripsi' => 'Seminar Skripsi',
    'publikasi' => 'Tahap Publikasi'
];
?>

<?php foreach ($status_labels as $key => $label): ?>
    <tr height="20" style="height:15.0pt">
        <td colspan="2" height="20" class="xl66" style="height:15.0pt;"><?= $label ?>:</td>
        <td class="xl66" style="font-weight:bold;"><?= isset($status_summary[$key]) ? $status_summary[$key] : 0 ?></td>
        <td colspan="8" class="xl66">&nbsp;</td>
    </tr>
<?php endforeach; ?>

<!-- Footer Info -->
<tr height="20" style="height:15.0pt">
<td colspan="11" height="20" style="height:15.0pt;">&nbsp;</td>
</tr>

<tr height="20" style="height:15.0pt">
<td colspan="11" height="20" style="height:15.0pt;font-size:9pt;color:gray;text-align:center;">
Data diekspor dari Sistem Informasi Manajemen Tugas Akhir (SIM-TA)<br>
STK Santo Yakobus Merauke - <?= date('Y') ?>
</td>
</tr>

</table>
</body>
</html>