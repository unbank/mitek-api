<?php

namespace Unbank\Identity\Mitek;

use Carbon\Carbon;
use Unbank\Identity\Mitek\Traits\CurlTrait;

/**
 * PHP Library for Mitek API
 *
 * Mitek uses OAuth v2 with JWT tokens for authorization and OpenID for authentication.
 * This token-based standard leverages temporary tokens that provide access to a resource for
 * a limited duration.
 */
class MitekAPI
{

    use CurlTrait;

    protected $client_id;
    protected $client_secret;
    protected $grant_type;
    protected $scope;
    protected $token = null;

    protected $api_url = 'https://api.west-1.us.mitekcloud.com';
    protected $production_api_url = 'https://api.west-1.us.mitekcloud.com';
    protected $sandbox_api_url = 'https://api.sandbox.west-1.us.mitekcloud.com';


    /**
     * Construct for the MitekAPI class
     *
     * @param string $token         Mitek Auth Token. If no, token is provided, execute the MitekAPI::auth function to generate a new token.
     * @param boolean $sandbox      Use Mitek Sandbox API URL instead of the Production API URL if set to `true`. Default: `false`
     */
    public function __construct($token = null, $sandbox=false)
    {
        $this->token = $token;
        $this->api_url = ( $sandbox )? $this->sandbox_api_url : $this->production_api_url;
    }

    /**
     * Get the authenication token for Mitek API.
     *
     * @link https://docs.us.mitekcloud.com/#authentication-request
     *
     * @param string $client_id         Client ID for Mitek API
     * @param string $client_secret     Client Secret for Mitek API
     * @param string $grant_type        OAuth credential type. Default client_credential
     * @param string $scope             API Scope for Mitek services. See https://docs.us.mitekcloud.com/#api-scopes for more details.
     * @return array                    An array of the authentication response from Mitek that includes the token to be used in
     *                                  subsequent requests and the number of seconds the token is valid for.
     */
    public function auth(
        string $client_id,
        string $client_secret,
        string $grant_type='client_credentials',
        string $scope='global.identity.api dossier.creator')
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

    /**
     * Convert file to Base64
     *
     * @param string $path      Path to the file.
     * @return string           Base64 data
     */
    public static function imageToBase64($path) {
        $data = file_get_contents($path);
        return base64_encode($data);
    }

    /**
     * Verify Document
     *
     * @link https://docs.us.mitekcloud.com/#verify-auto-response
     *
     * @param array $images                     A base64-encoded image containing a single page of the
     *                                          selfie to be used for face comparison and face liveness
     * @param string $customer_reference_id     [OPTIONAL] Customer provided identifier that will be
     *                                          returned in an identically named field within the body of any subsequent responses
     * @param string $docType                   Type of evidence to be processed.
     * @return array                            Returns Mitek API Verify Auto - Response as an array.
     */
    public function verify(array $images, string $selfie=null, string $customer_reference_id='', string $docType='IdDocument')
    {

        if ( !empty($customer_reference_id) ) {
            for ($i=0; $i < count($images) ; $i++) {
                $images[$i]["dossierMetadata"] = [
                    "customerReferenceId" => $customer_reference_id
                ];
            }
        }

        $has_selfie = '';
        $biometric = '';
        if ( !empty($selfie) ) {
            $biometric = '{
                "type": "Biometric",
                "biometricType": "Selfie",
                "data": "'. $selfie .'"
              }
            ';
            $has_selfie = ',
            "configuration": {
                "verifications": {
                  "faceComparison": true,
                  "dataSignalAAMVA": true
                }
              }';
        }
        $images_json = json_encode($images);

        $postData = '{
            '.( ( !empty($customer_reference_id) )? "\"customerReferenceId\": \"$customer_reference_id\"," : '' ).'
            "evidence": [
                  {
                    "type": "'.$docType.'",
                    "images": '.$images_json.'
                }
                '.( (!empty($biometric))? ", $biometric" : '' ).'
            ]'.$has_selfie.'
        }';

        // $user = \Auth::user();
        // file_put_contents("/var/www/html/resources/assets/mitek/request-user-$user->id-data.json", $postData);
        $data = $this->request("$this->api_url/api/verify/v2/dossier", $postData);
        return $data;
    }

}

?>
