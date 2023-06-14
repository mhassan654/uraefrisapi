<?php

use Mhassan654\Uraefrisapi\Http\Controllers\EfrisController;

Route::get('/server-info', [EfrisController::class, 'getServerInfo']);

Route::get('/server-time', [EfrisController::class, 'T101']);

Route::get('/registration-details', [EfrisController::class, 'T103']);

Route::get('/invoice-details/{invoice_no}', [EfrisController::class, 'T108']);

Route::get('/exchange-rates', [EfrisController::class, 'T126']);

Route::get('/exchange-rate/{currency}', [EfrisController::class, 'T121']);

Route::get('/goods-and-services', [EfrisController::class, 'T127']);
Route::post('/goods-and-services', [EfrisController::class, 'T127']);

Route::get('/master-data', [EfrisController::class, 'T115']);

Route::post('/register-product', [EfrisController::class, 'T130']);

Route::post('/inquire-invoice', [EfrisController::class, 'T107']);

Route::post('/increase-stock', [EfrisController::class, 'T131up']);

Route::post('/decrease-stock', [EfrisController::class, 'T131down']);

Route::post('/invoice-receipt-query', [EfrisController::class, 'T106']);

Route::post('/search-taxpayer', [EfrisController::class, 'T119']);

Route::get('/unspsc-master', [EfrisController::class, 'T124']);

Route::post('/stock-quantity', [EfrisController::class, 'T128']);

Route::post('/generate-fiscal-invoice', [EfrisController::class, 'T109']);

Route::post('/generate-fiscal-invoice-preview', [EfrisController::class, 'T109Preview']);

Route::post('/generate-bulk-invoices', [EfrisController::class, 'T109Bulk']);

Route::post('/generate-fiscal-receipt', [EfrisController::class, 'T109']);

Route::post('/generate-fiscal-receipt-preview', [EfrisController::class, 'T109Preview']);

Route::get('/excise-duty', [EfrisController::class, 'T125']);

Route::post('/sync-products', [EfrisController::class, 'synchProductsDatabase']);

Route::post('/manufacturer-stockin', [EfrisController::class, 'manufacturerStockIn']);

Route::get('/unspsc-codes', [EfrisController::class, 'T124Unspsc']);

Route::post('/product-details', [EfrisController::class, 'T144']);

Route::post('/transfer-stock', [EfrisController::class, 'T139']);

Route::post('/stock-movement-history', [EfrisController::class, 'T145']);

Route::get('/qrcode/{invoice_no}', [EfrisController::class, 'QrCode']);

Route::get('/creditnote-details/{noteId}', [EfrisController::class, 'T118']);

Route::post('/apply-for-creditnote', [EfrisController::class, 'T110']);

Route::post('/query-creditnotes', [EfrisController::class, 'T111']);
Route::post('/search-creditnotes', [EfrisController::class, 'T111']);

Route::get('/creditnote-details/{id}', [EfrisController::class, 'T112']);

Route::post('/approve-creditnote', [EfrisController::class, 'T113']);

Route::post('/cancel-creditnote', [EfrisController::class, 'T114']);

Route::get('/logs/all', [EfrisController::class, 'getLogs']);
