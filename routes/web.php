<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
});

Volt::route('/pos', 'pos-terminal');
Route::get('/invoices/{invoice}/download', function (\App\Models\Invoice $invoice) {
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'autoScriptToLang' => true,
        'autoLangToFont' => true,
    ]);

    $html = view('invoices.pdf', ['invoice' => $invoice])->render();
    $mpdf->WriteHTML($html);

    return response()->streamDownload(function () use ($mpdf) {
        echo $mpdf->Output('', 'S');
    }, "invoice-{$invoice->id}.pdf");
})->name('invoices.download')->middleware('auth'); // حماية المسار للمسجلين فقط
