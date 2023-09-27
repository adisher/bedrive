<?php

namespace Common\Files\S3;

use Carbon\Carbon;
use Common\Core\BaseController;
use Common\Files\Actions\ValidateFileUpload;

class S3SimpleUploadController extends BaseController
{
    use InteractsWithS3Api;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function presignPost()
    {
        $fileKey = $this->buildFileKey();

        $errors = app(ValidateFileUpload::class)->execute(request()->all());
        if ($errors) {
            abort(422, $errors->first());
        }

        $command = $this->getClient()->getCommand('PutObject', [
            'Bucket' => $this->getBucket(),
            'ContentType' => request()->input('mime'),
            'Key' => $fileKey,
            'ACL' => $this->getAcl(),
        ]);

        $uri = $this->getClient()
            ->createPresignedRequest($command, Carbon::now()->addHour())
            ->getUri();

        return $this->success([
            'url' => $uri,
            'key' => $fileKey,
            'acl' => $this->getAcl(),
        ]);
    }
}
