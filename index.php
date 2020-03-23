<?php
//Tetapkan kunci server Anda (Catatan: Kunci server untuk mode sandbox dan mode produksi berbeda)
$server_key = 'SB-Mid-server-ATxdnj03T4ldfCw5Lb5nET2C';

// Set true untuk produksi, set false untuk sandbox
$is_production = false;
$api_url = $is_production ? 
  'https://app.midtrans.com/snap/v1/transactions' : 
  'https://app.sandbox.midtrans.com/snap/v1/transactions';

// Periksa apakah permintaan tidak mengandung `/charge (biaya)` di url / path, tampilkan 404
if( !strpos($_SERVER['REQUEST_URI'], '/charge') ) {
  http_response_code(404); 
  echo "wrong path, make sure it's `/charge`"; exit();
}
// Periksa apakah metode ini bukan HTTP POST, tampilkan 404
if( $_SERVER['REQUEST_METHOD'] !== 'POST'){
  http_response_code(404);
  echo "Page not found or wrong HTTP request method is used"; exit();
  //Halaman tidak ditemukan atau metode permintaan HTTP yang salah digunakan
}
// dapatkan isi HTTP POST dari permintaan (request)
$request_body = file_get_contents('php://input');
// setel jenis konten respons sebagai JSON
header('Content-Type: application/json');
// API biaya panggilan menggunakan body permintaan yang disahkan oleh SDK seluler
$charge_result = chargeAPI($api_url, $server_key, $request_body);
// mengatur kode status http response (tanggapan)
http_response_code($charge_result['http_code']);
// then print out the response body
echo $charge_result['body'];
/**
 * call charge API using Curl
 * @param string  $api_url
 * @param string  $server_key
 * @param string  $request_body
 */
function chargeAPI($api_url, $server_key, $request_body){
  $ch = curl_init();
  $curl_options = array(
    CURLOPT_URL => $api_url,
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_POST => 1,
    CURLOPT_HEADER => 0,
    // Tambahkan heder ke permintaan, termasuk Otorisasi yang dihasilkan dari kunci server
    CURLOPT_HTTPHEADER => array(
      'Content-Type: application/json',
      'Accept: application/json',
      'Authorization: Basic ' . base64_encode($server_key . ':')
    ),
    CURLOPT_POSTFIELDS => $request_body
  );
  curl_setopt_array($ch, $curl_options);
  $result = array(
    'body' => curl_exec($ch),
    'http_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
  );
  return $result;
}