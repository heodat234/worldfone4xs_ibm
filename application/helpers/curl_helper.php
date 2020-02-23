<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function curl_get_file_size( $url ) {
    // Assume failure.
    $result = -1;

    $curl = curl_init( $url );

    // Issue a HEAD request and follow any redirects.
    curl_setopt( $curl, CURLOPT_NOBODY, true );
    curl_setopt( $curl, CURLOPT_HEADER, true );
    curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );

    $data = curl_exec( $curl );
    curl_close( $curl );

    if( $data ) {
      $content_length = "unknown";
      $status = "unknown";

      if( preg_match( "/^HTTP\/1\.[01] (\d\d\d)/", $data, $matches ) ) {
        $status = (int)$matches[1];
      }

      if( preg_match( "/Content-Length: (\d+)/", $data, $matches ) ) {
        $content_length = (int)$matches[1];
      }

      // http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
      if( $status == 200 || ($status > 300 && $status <= 308) ) {
        $result = $content_length;
      }
    }

    return $result;
}