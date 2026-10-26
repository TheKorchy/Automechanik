<?php
require_once 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

// 1. Zber dát (zjednotené s tvojím formulárom)
$id         = $_REQUEST['id'] ?? rand(100, 999);
$name       = $_REQUEST['name'] ?? 'Meno';
$l_name     = $_REQUEST['l_name'] ?? 'Priezvisko';
$email      = $_REQUEST['email'] ?? '';
$service    = $_REQUEST['service'] ?? 'Servisný úkon';
$price_total = (float)($_REQUEST['price'] ?? 0);

// Výpočet DPH (20%)
$dph_rate = 0.20;
$price_base = $price_total / (1 + $dph_rate);
$dph_value = $price_total - $price_base;

$zakaznik = trim("$name $l_name");
$datum_vystavenia = date("d.m.Y");
$datum_dodania = date("d.m.Y");
$splatnost = date("d.m.Y", strtotime("+14 days"));

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');
$dompdf = new Dompdf($options);

$html = "
<!DOCTYPE html>
<html lang='sk'>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #1e293b; margin: 0; padding: 0; }
        .invoice-container { padding: 40px; }
        
        /* Hlavička */
        .header-table { width: 100%; border-bottom: 3px solid #3b82f6; padding-bottom: 20px; margin-bottom: 30px; }
        .logo { font-size: 26px; font-weight: bold; color: #1e40af; }
        .invoice-title { font-size: 20px; text-align: right; text-transform: uppercase; color: #64748b; }

        /* Sekcia strán (Dodávateľ vs Odberateľ) */
        .sides-table { width: 100%; margin-bottom: 40px; border-spacing: 0; }
        .side-box { width: 50%; vertical-align: top; }
        .side-title { font-size: 10px; font-weight: bold; color: #3b82f6; text-transform: uppercase; margin-bottom: 8px; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; width: 90%; }
        
        /* Informácie o platbe */
        .details-table { width: 100%; background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 30px; }
        .details-table td { padding: 5px 0; }
        .label { color: #64748b; font-size: 11px; }

        /* Hlavná tabuľka s položkami */
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table th { background: #1e40af; color: white; padding: 10px; text-align: left; font-size: 11px; }
        .items-table td { padding: 12px 10px; border-bottom: 1px solid #e2e8f0; }

        /* Rekapitulácia sumy */
        .summary-wrapper { width: 100%; }
        .summary-table { width: 250px; float: right; }
        .summary-table td { padding: 8px; font-size: 13px; }
        .total-row { background: #3b82f6; color: white; font-weight: bold; font-size: 16px !important; }

        .footer { position: fixed; bottom: 30px; width: 100%; text-align: center; color: #94a3b8; font-size: 10px; border-top: 1px solid #e2e8f0; padding-top: 10px; }
    </style>
</head>
<body>
    <div class='invoice-container'>
        <table class='header-table'>
            <tr>
                <td class='logo'>AUTOSERVIS TORYSA</td>
                <td class='invoice-title'>Faktúra č. 2026" . str_pad($id, 4, "0", STR_PAD_LEFT) . "</td>
            </tr>
        </table>

        <table class='sides-table'>
            <tr>
                <td class='side-box'>
                    <div class='side-title'>Dodávateľ</div>
                    <strong>Autoservis Torysa s.r.o.</strong><br>
                    Slovenská ulica 12<br>
                    080 01 Prešov, Slovensko<br><br>
                    IČO: 12345678<br>
                    DIČ: 2021234567<br>
                    IČ DPH: SK2021234567
                </td>
                <td class='side-box'>
                    <div class='side-title'>Odberateľ</div>
                    <strong>$zakaznik</strong><br>
                    Slovenská republika<br>
                    $email<br><br>
                    <span class='label'>Spôsob úhrady:</span> Prevodom
                </td>
            </tr>
        </table>

        <table class='details-table'>
            <tr>
                <td><span class='label'>Dátum vystavenia:</span><br><strong>$datum_vystavenia</strong></td>
                <td><span class='label'>Dátum dodania:</span><br><strong>$datum_dodania</strong></td>
                <td><span class='label'>Dátum splatnosti:</span><br><strong style='color: #ef4444;'>$splatnost</strong></td>
            </tr>
            <tr>
                <td colspan='3' style='padding-top: 15px;'>
                    <span class='label'>IBAN:</span> <strong>SK12 0000 0000 0012 3456 7890</strong><br>
                    <span class='label'>Variabilný symbol:</span> <strong>2026" . str_pad($id, 4, "0", STR_PAD_LEFT) . "</strong>
                </td>
            </tr>
        </table>

        <table class='items-table'>
            <thead>
                <tr>
                    <th>Popis položky</th>
                    <th style='text-align: center;'>DPH</th>
                    <th style='text-align: right;'>Základ</th>
                    <th style='text-align: right;'>Spolu</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>$service</td>
                    <td style='text-align: center;'>20 %</td>
                    <td style='text-align: right;'>" . number_format($price_base, 2, ',', ' ') . " €</td>
                    <td style='text-align: right;'>" . number_format($price_total, 2, ',', ' ') . " €</td>
                </tr>
            </tbody>
        </table>

        <div class='summary-wrapper'>
            <table class='summary-table'>
                <tr>
                    <td>Základ dane:</td>
                    <td style='text-align: right;'>" . number_format($price_base, 2, ',', ' ') . " €</td>
                </tr>
                <tr>
                    <td>DPH 20 %:</td>
                    <td style='text-align: right;'>" . number_format($dph_value, 2, ',', ' ') . " €</td>
                </tr>
                <tr class='total-row'>
                    <td>CELKOM:</td>
                    <td style='text-align: right;'>" . number_format($price_total, 2, ',', ' ') . " €</td>
                </tr>
            </table>
        </div>

        <div class='footer'>
            Registrácia: Obchodný register Okresného súdu Prešov, oddiel Sro, vložka č. 12345/P<br>
            <strong>Ďakujeme za využitie našich služieb!</strong>
        </div>
    </div>
</body>
</html>";

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Faktura_Torysa_Elite_$id.pdf", ["Attachment" => 1]);
exit;