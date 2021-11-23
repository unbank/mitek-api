<?php

namespace Unbank\Identity\Mitek;

use Carbon\Carbon;


/**
 * PHP Library for Mitek API
 *
 * Mitek uses OAuth v2 with JWT tokens for authorization and OpenID for authentication.
 * This token-based standard leverages temporary tokens that provide access to a resource for
 * a limited duration.
 */
class AgentAPI extends MitekAPI
{

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
        string $scope='standard.scope additional.scope')
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->grant_type = $grant_type;
        $this->scope = $scope;

        $params = compact('client_id', 'client_secret', 'grant_type', 'scope');
        $params = http_build_query($params);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "$this->api_url/oauth2/token",
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
     * Manual Agent Verify Request
     *
     * @link https://developer.us.mitekcloud.com/#mobile-verify-manual
     *
     * @param array $images                     A base64-encoded image containing a single page of the
     *                                          selfie to be used for face comparison and face liveness
     * @param string $transactionRequestId       A valid UUID for a previously submitted Mobile Verify Auto request
     * @param string $customer_reference_id     [OPTIONAL] Customer provided identifier that will be
     *                                          returned in an identically named field within the body of any subsequent responses
     * @param string $deviceExtractedData       Type of evidence to be processed.
     * @return array                            Returns Mitek API Verify Auto - Response as an array.
     */
    public function agent_verify(string $transactionRequestId, array $images, string $customer_reference_id='', string $deviceExtractedData=null)
    {
        $postData = [
            "images" => $images
        ];

        if( !empty($transactionRequestId) ) {
            $postData["transactionRequestId"] = $transactionRequestId;
        }
        if ( !empty($customer_reference_id) ) {
            $postData["customerReferenceId"] = $customer_reference_id;
        }

        if ( !empty($deviceExtractedData ) ) {
            $postData["deviceExtractedData"] = $deviceExtractedData;
        }

        $postDataStr = json_encode($postData);
        $data = $this->request("$this->api_url/identity/verify/v3/id-document/manual", $postDataStr);
        return $data;
    }


    /**
     * Manual Agent Retrieval Request
     *
     * @link https://developer.us.mitekcloud.com/#manual-retrieval-request
     *
     * @param string $retrievalId       A valid UUID for a previously submitted Mobile Verify Auto request
     * @return array                            Returns Mitek API Verify Auto - Response as an array.
     */
    public function agent_retrieval(string $retrievalId)
    {
        $data = $this->getRequest("$this->api_url/identity/verify/v3/id-document/manual/$retrievalId");
        return $data;
    }


    /**
     * Expert Request
     *
     * Each expert processing request is for the evaluation of a single document that can consist of
     * one to many images depending on the type of document being processed.
     *
     * @link https://developer.us.mitekcloud.com/#mobile-verify-manual
     *
     * @param array $images                     A base64-encoded image containing a single page of the
     *                                          selfie to be used for face comparison and face liveness
     * @param string $transactionRequestId       A valid UUID for a previously submitted Mobile Verify Auto request
     * @param string $customer_reference_id     [OPTIONAL] Customer provided identifier that will be
     *                                          returned in an identically named field within the body of any subsequent responses
     * @param string $deviceExtractedData       Type of evidence to be processed.
     * @return array                            Returns Mitek API Verify Auto - Response as an array.
     */
    public function expert_verify(array $images, string $transactionRequestId=null, string $customer_reference_id='', string $deviceExtractedData=null)
    {
        $postData = [
            "images" => $images
        ];

        if( !empty($transactionRequestId) ) {
            $postData["transactionRequestId"] = $transactionRequestId;
        }
        if ( !empty($customer_reference_id) ) {
            $postData["customerReferenceId"] = $customer_reference_id;
        }

        if ( !empty($deviceExtractedData ) ) {
            $postData["deviceExtractedData"] = $deviceExtractedData;
        }

        $postDataStr = json_encode($postData);
        $data = $this->request("$this->api_url/identity/verify/v3/id-document/expert", $postDataStr);
        return $data;
    }


    /**
     * Face Comparison - Auto Request
     *
     * Each expert processing request is for the evaluation of a single document that can consist of
     * one to many images depending on the type of document being processed.
     *
     * @link https://developer.us.mitekcloud.com/#face-comparison-manual-request
     *
     * @param array $images                     A base64-encoded image containing a single page of the
     *                                          selfie to be used for face comparison and face liveness
     * @param string $transactionRequestId       A valid UUID for a previously submitted Mobile Verify Auto request
     * @param string $customer_reference_id     [OPTIONAL] Customer provided identifier that will be
     *                                          returned in an identically named field within the body of any subsequent responses
     * @param string $deviceExtractedData       Type of evidence to be processed.
     * @return array                            Returns Mitek API Verify Auto - Response as an array.
     */
    public function face_comparison_auto_request(string $transactionRequestId, array $images, string $selfie, string $customer_reference_id='') {
        $postData = [
            "transactionRequestId" => $transactionRequestId,
            "referenceImages" => $images,
            "selfieImages" => [
                [
                    "data" => $selfie
                ]
            ]
        ];

        if ( !empty($customer_reference_id) ) {
            $postData["customerReferenceId"] = $customer_reference_id;
        }

        $postDataStr = json_encode($postData);
        $data = $this->request("$this->api_url/identity/facecomparison/v3/auto", $postDataStr);
        return $data;
    }

    /**
     * Face Comparison - Manual Request
     *
     * Each expert processing request is for the evaluation of a single document that can consist of
     * one to many images depending on the type of document being processed.
     *
     * @link https://developer.us.mitekcloud.com/#face-comparison-manual-request
     *
     * @param array $images                     A base64-encoded image containing a single page of the
     *                                          selfie to be used for face comparison and face liveness
     * @param string $transactionRequestId       A valid UUID for a previously submitted Mobile Verify Auto request
     * @param string $customer_reference_id     [OPTIONAL] Customer provided identifier that will be
     *                                          returned in an identically named field within the body of any subsequent responses
     * @param string $deviceExtractedData       Type of evidence to be processed.
     * @return array                            Returns Mitek API Verify Auto - Response as an array.
     */
    public function face_comparison_manual_request(string $transactionRequestId, array $images, string $selfie, string $customer_reference_id='') {
        $postData = [
            "transactionRequestId" => $transactionRequestId,
            "referenceImages" => $images,
            "selfieImages" => [
                [
                    "data" => $selfie
                ]
            ]
        ];

        if ( !empty($customer_reference_id) ) {
            $postData["customerReferenceId"] = $customer_reference_id;
        }

        $postDataStr = json_encode($postData);
        $data = $this->request("$this->api_url/identity/facecomparison/v3/manual", $postDataStr);
        return $data;
    }

    /**
     * Face Comparison - Manual Retrieval Request
     *
     * Results retrieval is the final step in retrieving the final results from a manual request.
     *
     * @link https://developer.us.mitekcloud.com/#face-comparison-manual-response
     *
     * @param string $transactionRequestId       A valid UUID for a previously submitted Mobile Verify Auto request
     * @return array                            Returns Mitek API Verify Auto - Response as an array.
     */
    public function face_comparison_retrieval(string $transactionRequestId)
    {
        $data = $this->getRequest("$this->api_url/identity/facecomparison/v3/manual/$transactionRequestId");
        return $data;
    }


    /**
     * Expert Retrieval Request
     *
     * Results retrieval is the final step in expert review processing.
     *
     * @link https://developer.us.mitekcloud.com/#manual-retrieval-request
     *
     * @param string $transactionRequestId       A valid UUID for a previously submitted Mobile Verify Auto request
     * @return array                            Returns Mitek API Verify Auto - Response as an array.
     */
    public function expert_retrieval(string $transactionRequestId)
    {
        $data = $this->getRequest("$this->api_url/identity/verify/v3/id-document/expert/$transactionRequestId");
        return $data;
    }



    /**
     * Polling is implemented as a simple GET operation on the polling end-point and will
     * return a list of current transactions for the tenant with details of their current status:
     * (PROCESSING, COMPLETED, or ERROR).
     *
     * @link https://developer.us.mitekcloud.com/#polling-request
     *
     * @param string $transactionRequestId       A valid UUID for a previously submitted Mobile Verify Auto request
     * @return array                            Returns Mitek API Verify Auto - Response as an array.
     */
    public function agent_polling()
    {
        $data = $this->getRequest("$this->api_url/identity/v3/poll");
        return $data;
    }


}

?>
