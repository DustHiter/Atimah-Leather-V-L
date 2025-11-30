<?php
function pexels_key() {
  $k = getenv('PEXELS_KEY');
  return $k && strlen($k) > 0 ? $k : 'Vc99rnmOhHhJAbgGQoKLZtsaIVfkeownoQNbTj78VemUjKh08ZYRbf18';
}
function pexels_get($url) {
  $ch = curl_init();
  curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [ 'Authorization: '. pexels_key() ],
    CURLOPT_TIMEOUT => 15,
  ]);
  $resp = curl_exec($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  if ($code >= 200 && $code < 300 && $resp) return json_decode($resp, true);
  return null;
}
function download_to($srcUrl, $destPath) {
  $data = file_get_contents($srcUrl);
  if ($data === false) return false;
  if (!is_dir(dirname($destPath))) mkdir(dirname($destPath), 0775, true);
  return file_put_contents($destPath, $data) !== false;
}