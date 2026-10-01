<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('spd/cal-data', 'SpdController::calData');
$routes->post('spd/save', 'SpdController::save');
$routes->get('spd/delete/(:num)', 'SpdController::delete/$1');
$routes->get('spd/edit/(:num)', 'SpdController::edit/$1');
$routes->post('spd/update/(:num)', 'SpdController::update/$1');
$routes->get('spd/export-excel', 'SpdController::exportExcel');
$routes->get('spd/print/(:num)', 'SpdController::printSpd/$1');
// Route Authentication
$routes->get('login', 'AuthController::login');
$routes->post('login-process', 'AuthController::loginProcess');
$routes->get('register', 'AuthController::register');
$routes->post('register-process', 'AuthController::registerProcess');
$routes->get('logout', 'AuthController::logout');
$routes->get('spd/riwayat', 'SpdController::riwayat');
$routes->get('spd/pdf-pekerja/(:num)', 'SpdController::pdfPekerja/$1');
$routes->get('spd/profil', 'SpdController::profil');
$routes->post('spd/update-profil', 'SpdController::updateProfil');
// Route Role Admin / Finance / PHR (Tanpa Group)
$routes->get('spd/admin-monitoring', 'SpdController::adminMonitoring');
$routes->post('spd/update-status', 'SpdController::updateStatus');
$routes->get('spd/export-excel', 'SpdController::exportExcel');
$routes->get('spd/pdf-admin/(:num)', 'SpdController::pdfAdmin/$1');
// Route Edit Pejabat TTD & Hapus SPD Admin
$routes->post('spd/update-pejabat', 'SpdController::updatePejabat');
$routes->get('spd/delete/(:num)', 'SpdController::delete/$1');
$routes->post('spd/kirim-revisi', 'SpdController::kirimRevisi');
$routes->post('spd/upload-bukti', 'SpdController::uploadBukti');
$routes->post('spd/revisi-submit', 'SpdController::revisiSubmit');
$routes->get('spd/kalkulasi-admin', 'SpdController::kalkulasiAdmin');
// Fitur Lupa Password & Restrikasi Admin
$routes->get('forgot-password', 'AuthController::forgotPassword');
$routes->post('forgot-password/send', 'AuthController::sendResetToken');
$routes->get('reset-password/(:segment)', 'AuthController::resetPassword/$1');
$routes->post('reset-password/update', 'AuthController::updatePassword');