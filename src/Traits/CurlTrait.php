<?php

namespace Unbank\Identity\Mitek\Traits;

trait CurlTrait {


    /**
     * Send API request
     *
     * @uses Mitek::auth        Get the authentication token from the response.
     * @param [type] $url       API Endpoint
     * @param [type] $postData  HTTP Post Data
     * @return array            Returns Mitek JSON repsonse as an array.
     */
    protected function request($url, $data=null, $method="POST") {
        if ( $method == "POST" ) {
            return $this->postRequest($url, $data);
        }
        return $this->getRequest($url);
    }

    /**
     * Send POST API request
     *
     * @uses Mitek::auth        Get the authentication token from the response.
     * @param string $url       API Endpoint
     * @param mixed $postData  HTTP Post Data
     * @return array            Returns Mitek JSON repsonse as an array.
     */
    protected function postRequest(string $url, $postData) {

        if ( str_contains($url, "/identity/facecomparison/v3/manual") ) {

            dd($url, array(
                'Content-Type: application/json',
                'Authorization: Bearer '.$this->token
            ), $postData);
        }
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => $postData,
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer '.$this->token
          ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, true);
    }


    /**
     * Send Get API request
     *
     * @uses Mitek::auth        Get the authentication token from the response.
     * @param [type] $url       API Endpoint
     * @param [type] $postData  HTTP Post Data
     * @return array            Returns Mitek JSON repsonse as an array.
     */
    protected function getRequest($url) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer '.$this->token
          ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, true);
    }
}


?>
