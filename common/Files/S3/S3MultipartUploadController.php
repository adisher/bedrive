<?php

namespace Common\Files\S3;

use Carbon\Carbon;
use Common\Core\BaseController;
use Common\Files\Actions\ValidateFileUpload;

class S3MultipartUploadController extends BaseController
{
    use InteractsWithS3Api;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create()
    {
        $errors = app(ValidateFileUpload::class)->execute(request()->all());
        if ($errors) {
            abort(422, $errors->first());
        }

        $result = $this->getClient()->createMultipartUpload([
            'Key' => $this->buildFileKey(),
            'Bucket' => $this->getBucket(),
            'ContentType' => request()->input('mime'),
            'ACL' => $this->getAcl(),
        ]);

        return $this->success([
            'key' => $result['Key'],
            'uploadId' => $result['UploadId'],
            'acl' => $this->getAcl(),
        ]);
    }

    public function getUploadedParts()
    {
        $data = $this->getClient()->listParts([
            'Bucket' => $this->getBucket(),
            'Key' => request('key'),
            'UploadId' => request('uploadId'),
            'PartNumberMarker' => 0,
        ]);

        return $this->success([
            'parts' => $data['Parts'],
        ]);
    }

    public function batchSignPartUrls()
    {
        $partNumbers = request()->input('partNumbers');

        $urls = [];

        foreach ($partNumbers as $partNumber) {
            $url = $this->getPartUrl(
                $partNumber,
                request('uploadId'),
                request('key'),
            );
            $urls[] = ['url' => $url, 'partNumber' => $partNumber];
        }

        return $this->success([
            'urls' => $urls,
        ]);
    }

    public function complete()
    {
        $data = $this->getClient()->completeMultipartUpload([
            'Bucket' => $this->getBucket(),
            'Key' => request()->input('key'),
            'UploadId' => request()->input('uploadId'),
            'MultipartUpload' => [
                'Parts' => request()->input('parts'),
            ],
        ]);

        return $this->success([
            'location' => $data['Location'],
        ]);
    }

    public function abort()
    {
        $this->getClient()->abortMultipartUpload([
            'Bucket' => $this->getBucket(),
            'Key' => request()->input('key'),
            'UploadId' => request()->input('uploadId'),
        ]);

        return $this->success();
    }

    protected function getPartUrl(
        string $partNumber,
        string $uploadId,
        string $key
    ): string {
        $command = $this->getClient()->getCommand('UploadPart', [
            'Bucket' => $this->getBucket(),
            'Key' => $key,
            'UploadId' => $uploadId,
            'PartNumber' => $partNumber,
        ]);
        $s3Request = $this->getClient()->createPresignedRequest(
            $command,
            Carbon::now()->addMinutes(30),
        );

        return (string) $s3Request->getUri();
    }
}
