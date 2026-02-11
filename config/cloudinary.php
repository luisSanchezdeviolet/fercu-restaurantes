<?php
class CloudinaryUploader
{
    private $cloudName;
    private $apiKey;
    private $apiSecret;
    private $uploadPreset;

    public function __construct()
    {
        $this->cloudName = 'cloudName'; // Debe ser el nombre de tu cuenta de Cloudinary
        $this->apiKey = 'apiKey'; // Debe ser una clave de API válida de Cloudinary
        $this->apiSecret = 'apiSecret'; // Opcional, si no se usa firma
        $this->uploadPreset = 'uploadPreset'; // Debe ser un preset de subida configurado en Cloudinary
    }

    public function testConnection()
    {
        try {
            $url = "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/upload";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'CloudinaryUploader/1.0');

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);

            if ($errno !== 0) {
                return [
                    'success' => false,
                    'error' => "cURL Error #{$errno}: {$error}",
                    'suggestion' => 'Verifica tu conexión a internet y configuración SSL'
                ];
            }

            if ($httpCode === 400 || $httpCode === 200) {
                return [
                    'success' => true,
                    'message' => 'Conexión exitosa con Cloudinary',
                    'http_code' => $httpCode
                ];
            }

            return [
                'success' => false,
                'error' => "HTTP Error: {$httpCode}",
                'response' => substr($response, 0, 200)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    public function testUploadPreset()
    {
        try {
            $tempImage = tempnam(sys_get_temp_dir(), 'cloudinary_test');
            $imageData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
            file_put_contents($tempImage, $imageData);

            $testFile = [
                'tmp_name' => $tempImage,
                'type' => 'image/png',
                'size' => filesize($tempImage)
            ];

            $result = $this->uploadImage($testFile, 'test');

            unlink($tempImage);

            if ($result['success'] && isset($result['public_id'])) {
                $this->deleteImage($result['public_id']);
            }

            return $result;
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Test upload failed: ' . $e->getMessage()
            ];
        }
    }

    public function uploadImage($imageFile, $folder = 'categorias')
    {
        try {
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/svg+xml'];
            $fileType = $imageFile['type'];

            if (!in_array($fileType, $allowedTypes)) {
                return [
                    'success' => false,
                    'message' => 'Solo se permiten archivos de imagen (JPG, PNG, WEBP, SVG)'
                ];
            }

            if ($imageFile['size'] > 5 * 1024 * 1024) {
                return [
                    'success' => false,
                    'message' => 'El archivo no debe superar los 5MB'
                ];
            }

            if (!file_exists($imageFile['tmp_name'])) {
                return [
                    'success' => false,
                    'message' => 'Archivo temporal no encontrado'
                ];
            }

            $params = [
                'file' => new CURLFile($imageFile['tmp_name'], $fileType),
                'upload_preset' => $this->uploadPreset,
                'folder' => $folder
            ];

            $url = "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/upload";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'CloudinaryUploader/1.0');

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);

            if ($errno !== 0) {
                return [
                    'success' => false,
                    'message' => "Error de conexión: {$error}",
                    'curl_errno' => $errno
                ];
            }

            if ($httpCode === 200) {
                $result = json_decode($response, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    return [
                        'success' => false,
                        'message' => 'Error al decodificar respuesta JSON'
                    ];
                }

                return [
                    'success' => true,
                    'url' => $result['secure_url'],
                    'public_id' => $result['public_id'],
                    'message' => 'Imagen subida correctamente',
                    'width' => $result['width'] ?? null,
                    'height' => $result['height'] ?? null,
                    'format' => $result['format'] ?? null
                ];
            } else {
                $errorResult = json_decode($response, true);
                return [
                    'success' => false,
                    'message' => 'Error al subir la imagen a Cloudinary: ' . ($errorResult['error']['message'] ?? 'Error desconocido'),
                    'http_code' => $httpCode,
                    'response' => $response
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    public function uploadImageSigned($imageFile, $folder = 'categorias')
    {
        try {
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/svg+xml'];
            $fileType = $imageFile['type'];

            if (!in_array($fileType, $allowedTypes)) {
                return [
                    'success' => false,
                    'message' => 'Solo se permiten archivos de imagen (JPG, PNG, WEBP, SVG)'
                ];
            }

            if ($imageFile['size'] > 5 * 1024 * 1024) {
                return [
                    'success' => false,
                    'message' => 'El archivo no debe superar los 5MB'
                ];
            }

            if (!file_exists($imageFile['tmp_name'])) {
                return [
                    'success' => false,
                    'message' => 'Archivo temporal no encontrado'
                ];
            }

            $timestamp = time();
            $paramsToSign = [
                'folder' => $folder,
                'timestamp' => $timestamp
            ];
            $signature = $this->generateSignature($paramsToSign);

            $params = [
                'file' => new CURLFile($imageFile['tmp_name'], $fileType),
                'api_key' => $this->apiKey,
                'timestamp' => $timestamp,
                'signature' => $signature,
                'folder' => $folder
            ];

            $url = "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/upload";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'CloudinaryUploader/1.0');

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);

            if ($errno !== 0) {
                return [
                    'success' => false,
                    'message' => "Error de conexión: {$error}",
                    'curl_errno' => $errno
                ];
            }

            if ($httpCode === 200) {
                $result = json_decode($response, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    return [
                        'success' => false,
                        'message' => 'Error al decodificar respuesta JSON'
                    ];
                }

                return [
                    'success' => true,
                    'url' => $result['secure_url'],
                    'public_id' => $result['public_id'],
                    'message' => 'Imagen subida correctamente (signed)',
                    'width' => $result['width'] ?? null,
                    'height' => $result['height'] ?? null,
                    'format' => $result['format'] ?? null
                ];
            } else {
                $errorResult = json_decode($response, true);
                return [
                    'success' => false,
                    'message' => 'Error al subir la imagen a Cloudinary: ' . ($errorResult['error']['message'] ?? 'Error desconocido'),
                    'http_code' => $httpCode,
                    'response' => $response
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    public function deleteImage($publicId)
    {
        try {
            $timestamp = time();
            $params = [
                'public_id' => $publicId,
                'timestamp' => $timestamp
            ];

            $signature = $this->generateSignature($params);

            $postParams = [
                'public_id' => $publicId,
                'timestamp' => $timestamp,
                'api_key' => $this->apiKey,
                'signature' => $signature
            ];

            $url = "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/destroy";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postParams);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $response = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                return false;
            }

            $result = json_decode($response, true);
            return $result['result'] === 'ok';
        } catch (Exception $e) {
            return false;
        }
    }

    private function generateSignature($params)
    {
        unset($params['file']);
        unset($params['signature']);

        ksort($params);

        $paramString = '';
        foreach ($params as $key => $value) {
            if (!empty($value) || $value === 0) {
                $paramString .= $key . '=' . $value . '&';
            }
        }

        $paramString = rtrim($paramString, '&');
        $paramString .= $this->apiSecret;

        return sha1($paramString);
    }

    public function getConfig()
    {
        return [
            'cloud_name' => $this->cloudName,
            'api_key' => $this->apiKey,
            'upload_preset' => $this->uploadPreset,
            'api_secret_set' => !empty($this->apiSecret)
        ];
    }
}
