<?php namespace Common\Validation;

use Common\Core\BaseController;
use Common\Settings\Settings;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class RecaptchaController extends BaseController
{
    public function __construct(
        protected Request $request,
        protected Client $http,
        protected Settings $settings,
    ) {
    }

    public function verify()
    {
        $this->validate($this->request, [
            'token' => 'required|string',
        ]);

        $response = $this->http->post(
            'https://www.google.com/recaptcha/api/siteverify',
            [
                'form_params' => [
                    'response' => $this->request->get('token'),
                    'secret' => $this->settings->get('recaptcha.secret_key'),
                    'remoteip' => $this->request->getClientIp(),
                ],
            ],
        );

        $response = json_decode($response->getBody()->getContents(), true);

        $success = $response['success'] && $response['score'] > 0.1;

        return $this->success(['success' => $success]);
    }
}
