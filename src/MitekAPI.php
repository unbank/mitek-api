<?php

namespace Unbank\Identity\Mitek;

use Carbon\Carbon;

class MitekAPI
{

    protected $client_id;
    protected $client_secret;
    protected $grant_type;
    protected $scope;
    protected $token = null;

    protected $api_url = 'https://api.sandbox.west-1.us.mitekcloud.com';
    protected $production_api_url = 'https://api.sandbox.west-1.us.mitekcloud.com';
    protected $sandbox_api_url = 'https://api.sandbox.west-1.us.mitekcloud.com';

    public function __construct($token = null, $sandbox=false)
    {
        $this->token = $token;
        $this->api_url = ( $sandbox )? $this->sandbox_api_url : $this->production_api_url;
    }

    public function auth($client_id, $client_secret, $grant_type='client_credentials', $scope='global.identity.api dossier.creator')
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->grant_type = $grant_type;
        $this->scope = $scope;

        $params = compact('client_id', 'client_secret', 'grant_type', 'scope');
        $params = http_build_query($params);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "$this->api_url/connect/token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $data = json_decode($response, true);

        try {
            $this->token_expires = Carbon::now()->addMinutes(5);
            $this->token_response = json_decode($response, true);
            $this->token = $data['access_token'];
        } catch (\Throwable $th) {
            $data['log'] = "No access token was received";
        }
        return $data;
    }

    public static function imageToBase64($path) {
        $data = file_get_contents($path);
        return base64_encode($data);
    }

    protected function tokenRequest($url, $postData) {
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

    public function verify(array $images, string $customer_reference_id='', string $docType='IdDocument')
    {

        if ( !empty($customer_reference_id) ) {
            for ($i=0; $i < count($images) ; $i++) {
                $images[$i]["dossierMetadata"] = [
                    "customerReferenceId" => $customer_reference_id
                ];
            }
        }
        $images_json = json_encode($images);

        $postData = '{
            '.( ( !empty($customer_reference_id) )? "\"customerReferenceId\": \"$customer_reference_id\"," : '' ).'
            "evidence": [
                  {
                    "type": "'.$docType.'",
                    "images": '.$images_json.'
                }
            ]
        }';
        $data = $this->tokenRequest("$this->api_url/api/verify/v2/dossier", $postData);
        return $data;
    }

}

?>
