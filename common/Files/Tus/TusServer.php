<?php

namespace Common\Files\Tus;

use Carbon\Carbon;
use Common\Files\Actions\ValidateFileUpload;
use Common\Settings\Settings;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TusPhp\Exception\ConnectionException;
use TusPhp\Exception\FileException;
use TusPhp\Exception\OutOfRangeException;

class TusServer
{
    protected TusCache $cache;
    protected array $allowedHttpVerbs = [
        Request::METHOD_POST,
        Request::METHOD_PATCH,
        Request::METHOD_DELETE,
        Request::METHOD_HEAD,
        Request::METHOD_OPTIONS,
    ];

    protected const TUS_EXTENSIONS = [
        'creation',
        'termination',
        'checksum',
        'expiration',
    ];

    protected const TUS_PROTOCOL_VERSION = '1.0.0';
    protected const HEADER_CONTENT_TYPE = 'application/offset+octet-stream';
    protected const DEFAULT_CHECKSUM_ALGORITHM = 'sha256';
    protected const HTTP_CHECKSUM_MISMATCH = 460;

    public function __construct()
    {
        $this->cache = app(TusCache::class);
    }

    public function serve(): Response
    {
        $requestMethod = request()->method();

        if (!in_array($requestMethod, $this->allowedHttpVerbs, true)) {
            return $this->response(null, Response::HTTP_METHOD_NOT_ALLOWED);
        }

        $clientVersion = request()->header('Tus-Resumable');

        if (
            Request::METHOD_OPTIONS !== $requestMethod &&
            $clientVersion &&
            self::TUS_PROTOCOL_VERSION !== $clientVersion
        ) {
            return $this->response(null, Response::HTTP_PRECONDITION_FAILED, [
                'Tus-Version' => self::TUS_PROTOCOL_VERSION,
            ]);
        }

        return match (strtoupper($requestMethod)) {
            'OPTIONS' => $this->handleOptions(),
            'HEAD' => $this->handleHead(),
            'POST' => $this->handlePost(),
            'PATCH' => $this->handlePatch(),
            'DELETE' => $this->handleDelete(),
        };
    }

    protected function handleOptions(): Response
    {
        $supportedAlgorithms = hash_algos();

        $algorithms = [];
        foreach ($supportedAlgorithms as $hashAlgo) {
            if (str_contains($hashAlgo, ',')) {
                $algorithms[] = "'{$hashAlgo}'";
            } else {
                $algorithms[] = $hashAlgo;
            }
        }

        $headers = [
            'Allow' => implode(',', $this->allowedHttpVerbs),
            'Tus-Version' => self::TUS_PROTOCOL_VERSION,
            'Tus-Extension' => implode(',', self::TUS_EXTENSIONS),
            'Tus-Checksum-Algorithm' => implode(',', $algorithms),
        ];

        $maxUploadSize = app(Settings::class)->get('uploads.max_size');
        if ($maxUploadSize > 0) {
            $headers['Tus-Max-Size'] = $maxUploadSize;
        }

        return $this->response(null, Response::HTTP_OK, $headers);
    }

    protected function handleHead(): Response
    {
        $uploadKey = $this->getUploadKeyFromUrl();

        if (!($tusData = $this->cache->get($uploadKey))) {
            return $this->response(null, Response::HTTP_NOT_FOUND);
        }

        $offset = $tusData['offset'] ?? false;

        if ($offset === false) {
            return $this->response(null, Response::HTTP_GONE);
        }

        $headers = [
            'Upload-Length' => (int) $tusData['size'],
            'Upload-Offset' => (int) $tusData['offset'],
            'Cache-Control' => 'no-store',
        ];

        return $this->response(null, Response::HTTP_OK, $headers);
    }

    protected function handlePost(): Response
    {
        $meta = $this->extractMeta();
        $errors = app(ValidateFileUpload::class)->execute([
            'size' => $meta['clientSize'],
            'extension' => $meta['clientExtension'],
        ]);

        if ($errors) {
            return $this->response(
                json_encode(['message' => $errors->first()]),
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        $uploadKey = $this->getOrCreateUploadKey();
        $filePath = storage_path("tus/$uploadKey");

        $checksum = $this->getClientChecksum();
        $location = url("api/v1/tus/upload/$uploadKey");

        $expiresAt = now()->addDay();
        $formattedExpiresAt = $expiresAt->format('D, d M Y H:i:s \G\M\T');
        $this->cache->set(
            $uploadKey,
            [
                'size' => (int) request()->header('Upload-Length'),
                'offset' => 0,
                'checksum' => $checksum,
                'location' => $location,
                'file_path' => $filePath,
                'metadata' => $this->extractMeta(),
                'created_at' => Carbon::now()->timestamp,
                'expires_at' => $formattedExpiresAt,
            ],
            $expiresAt,
        );

        return $this->response(null, Response::HTTP_CREATED, [
            'Location' => $location,
            'Upload-Expires' => $formattedExpiresAt,
        ]);
    }

    protected function handlePatch(): Response
    {
        $uploadKey = $this->getUploadKeyFromUrl();

        if (!($tusData = $this->cache->get($uploadKey))) {
            return $this->response(null, Response::HTTP_GONE);
        }

        $status = $this->verifyPatchRequest($tusData);
        if (Response::HTTP_OK !== $status) {
            return $this->response(null, $status);
        }

        $checksum = $tusData['checksum'];
        $fileSize = $tusData['size'];

        try {
            $newOffset = (new TusFile([
                'upload_key' => $this->getUploadKeyFromUrl(),
                'total_bytes' => $tusData['size'],
                'file_path' => $tusData['file_path'],
                'offset' => $tusData['offset'],
            ]))->upload();

            if (
                $newOffset === $fileSize &&
                !$this->verifyChecksum($checksum, $tusData['file_path'])
            ) {
                return $this->response(null, self::HTTP_CHECKSUM_MISMATCH);
            }
        } catch (FileException $e) {
            return $this->response(
                $e->getMessage(),
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        } catch (OutOfRangeException) {
            return $this->response(
                null,
                Response::HTTP_REQUESTED_RANGE_NOT_SATISFIABLE,
            );
        } catch (ConnectionException) {
            return $this->response(null, Response::HTTP_CONTINUE);
        }

        if (!($tusData = $this->cache->get($uploadKey))) {
            return $this->response(null, Response::HTTP_GONE);
        }

        return $this->response(null, Response::HTTP_NO_CONTENT, [
            'Content-Type' => self::HEADER_CONTENT_TYPE,
            'Upload-Expires' => $tusData['expires_at'],
            'Upload-Offset' => $newOffset,
        ]);
    }

    protected function verifyPatchRequest(array $meta): int
    {
        $uploadOffset = request()->header('upload-offset');

        if ($uploadOffset && $uploadOffset !== (string) $meta['offset']) {
            return Response::HTTP_CONFLICT;
        }

        $contentType = request()->header('Content-Type');

        if ($contentType !== self::HEADER_CONTENT_TYPE) {
            return Response::HTTP_UNSUPPORTED_MEDIA_TYPE;
        }

        return Response::HTTP_OK;
    }

    protected function handleDelete(): Response
    {
        $uploadKey = $this->getUploadKeyFromUrl();
        $tusData = $this->cache->get($uploadKey);
        $resource = $tusData['file_path'] ?? null;

        if (!$resource) {
            return $this->response(null, Response::HTTP_NOT_FOUND);
        }

        $isDeleted = $this->cache->delete($uploadKey);

        if (!$isDeleted || !file_exists($resource)) {
            return $this->response(null, Response::HTTP_GONE);
        }

        unlink($resource);

        return $this->response(null, Response::HTTP_NO_CONTENT, [
            'Tus-Extension' => 'termination',
        ]);
    }

    protected function getClientChecksum(): string
    {
        $checksumHeader = request()->header('Upload-Checksum');

        if (empty($checksumHeader)) {
            return '';
        }

        [$checksumAlgorithm, $checksum] = explode(' ', $checksumHeader);

        $checksum = base64_decode($checksum);

        if (
            $checksum === false ||
            !in_array($checksumAlgorithm, hash_algos(), true)
        ) {
            abort(Response::HTTP_BAD_REQUEST);
        }

        return $checksum;
    }

    protected function verifyChecksum(string $checksum, string $filePath): bool
    {
        if (empty($checksum)) {
            return true;
        }

        return $checksum ===
            hash_file($this->getChecksumAlgorithm(), $filePath);
    }

    protected function getChecksumAlgorithm(): ?string
    {
        $checksumHeader = request()->header('Upload-Checksum');

        if (empty($checksumHeader)) {
            return self::DEFAULT_CHECKSUM_ALGORITHM;
        }

        [$checksumAlgorithm] = explode(' ', $checksumHeader);

        return $checksumAlgorithm;
    }

    protected function getOrCreateUploadKey(): string
    {
        if (!empty($this->uploadKey)) {
            return $this->uploadKey;
        }

        $key = request()->header('Upload-Key') ?? Uuid::uuid4()->toString();

        if (empty($key)) {
            abort(Response::HTTP_BAD_REQUEST);
        }

        $this->uploadKey = $key;

        return $this->uploadKey;
    }

    protected function extractMeta(): array
    {
        $uploadMetaData = request()->header('Upload-Metadata');

        if (empty($uploadMetaData)) {
            return [];
        }

        $uploadMetaDataChunks = explode(',', $uploadMetaData);

        $result = [];
        foreach ($uploadMetaDataChunks as $chunk) {
            $pieces = explode(' ', trim($chunk));

            $key = $pieces[0];
            $value = $pieces[1] ?? '';

            $result[$key] = base64_decode($value);
        }

        return $result;
    }

    function getUploadKeyFromUrl(): string
    {
        return basename(request()->getPathInfo());
    }

    protected function response(
        string $content = null,
        int $status = 200,
        array $headers = [],
    ): Response {
        $mergedHeaders = array_merge(
            [
                'X-Content-Type-Options' => 'nosniff',
                'Tus-Resumable' => self::TUS_PROTOCOL_VERSION,
                'Access-Control-Allow-Origin' => request()->header('Origin'),
                'Access-Control-Allow-Methods' => implode(
                    ',',
                    $this->allowedHttpVerbs,
                ),
                'Access-Control-Allow-Headers' =>
                    'Origin, X-Requested-With, Content-Type, Content-Length, Upload-Key, Upload-Checksum, Upload-Length, Upload-Offset, Tus-Version, Tus-Resumable, Upload-Metadata',
                'Access-Control-Expose-Headers' =>
                    'Upload-Key, Upload-Checksum, Upload-Length, Upload-Offset, Upload-Metadata, Tus-Version, Tus-Resumable, Tus-Extension, Location',
                'Access-Control-Max-Age' => 86400, // 24 hours
            ],
            $headers,
        );

        return response($content, $status, $mergedHeaders);
    }
}
