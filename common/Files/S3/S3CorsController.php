<?php

namespace Common\Files\S3;

use Common\Core\BaseController;
use Illuminate\Filesystem\AwsS3V3Adapter;
use Illuminate\Support\Facades\Storage;

class S3CorsController extends BaseController
{
    use InteractsWithS3Api;

    public function __construct()
    {
        $this->middleware('isAdmin');
    }

    public function uploadCors()
    {
        $cors = [
            [
                'AllowedOrigins' => [parse_url(config('app.url'), PHP_URL_HOST)],
                'AllowedMethods' => ['GET', 'HEAD', 'POST', 'PUT'],
                'MaxAgeSeconds' => 3000,
                'AllowedHeaders' => ['*'],
                'ExposeHeaders' => ['ETag'],
            ],
        ];

        $this->getClient()->putBucketCors([
            'Bucket' => $this->getBucket(),
            'CORSConfiguration' => [
                'CORSRules' => $cors,
            ],
        ]);

        return $this->success();
    }

    protected function getDiskName(): string
    {
        if (Storage::disk('uploads') instanceof AwsS3V3Adapter) {
            return 'uploads';
        }
        return 'public';
    }
}
