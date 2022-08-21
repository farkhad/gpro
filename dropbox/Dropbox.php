<?php

namespace Dropbox;

use GuzzleHttp\Client;

class Dropbox
{
    public const CLIENT_ID = "0x0pw4tp9bglmj2";
    public const UPLOAD_URL = "https://content.dropboxapi.com/2/files/upload";
    public const AUTHORIZE_URL = "https://dropbox.com/oauth2/authorize";
    public const TOKEN_URL = "https://api.dropboxapi.com/oauth2/token";
    public const REDIRECT_URI = "http://localhost:8000/dropbox";
    public const CREDS_FILE = 'creds.json';
    public const DROPBOX_REGISTRY = 'dropbox.json';

    public Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function toStorage($assoc)
    {
        foreach ($assoc as $key => $value) {
            $_SESSION['Dropbox'][$key] = $value;
        }
    }

    public function fromStorage($key)
    {
        return $_SESSION['Dropbox'][$key] ?? null;
    }

    public function unsetStorage($key)
    {
        unset($_SESSION['Dropbox'][$key]);
    }

    public function clearStorage()
    {
        $_SESSION['Dropbox'] = [];
    }

    public function getAuthUrl()
    {
        // Generate the code challenge using the OS / cryptographic random function
        $verifierBytes = random_bytes(64);
        $codeVerifier = rtrim(strtr(base64_encode($verifierBytes), "+/", "-_"), "=");

        // Very important, "raw_output" must be set to true or the challenge
        // will not match the verifier.
        $challengeBytes = hash("sha256", $codeVerifier, true);
        $codeChallenge = rtrim(strtr(base64_encode($challengeBytes), "+/", "-_"), "=");

        // State token, a uuid is fine here
        $state = uniqid();

        $this->toStorage([
            'codeVerifier' => $codeVerifier,
            'state' => $state
        ]);

        // Assemble the authorize URL and direct the user to a browser
        // to sign in to their AWeber customer account
        $authorizeQuery = array(
            "response_type" => "code",
            "token_access_type" => "offline",
            "client_id" => self::CLIENT_ID,
            "redirect_uri" => self::REDIRECT_URI,
            "state" => $state,
            "code_challenge" => $codeChallenge,
            "code_challenge_method" => "S256"
        );

        return self::AUTHORIZE_URL . "?" . http_build_query($authorizeQuery);
    }

    public function fetchTokenCreds($authorizationCode)
    {
        // Use the authorization code to fetch an access token
        $tokenQuery = array(
            "grant_type" => "authorization_code",
            "code" => $authorizationCode,
            "client_id" => self::CLIENT_ID,
            "code_verifier" => $this->fromStorage('codeVerifier'),
            "redirect_uri" => self::REDIRECT_URI,
        );

        $tokenUrl = self::TOKEN_URL . "?" . http_build_query($tokenQuery);
        $response = $this->client->post($tokenUrl);

        // Save the credentials to the creds.json file
        $body = $response->getBody();
        $this->storeTokenCreds($body);

        return json_decode($body, true);
    }

    public function refreshTokenCreds()
    {
        $creds = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . self::CREDS_FILE);
        $creds = json_decode($creds, true);
        $refreshToken = $creds['refresh_token'];

        $response = $this->client->post(
            self::TOKEN_URL, [
                'form_params' => [
                    'client_id' => self::CLIENT_ID,
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refreshToken
                ]
            ]
        );
        $body = $response->getBody();

        $creds = json_decode($body, true);
        $creds['refresh_token'] = $refreshToken;

        $this->storeTokenCreds(json_encode($creds));
    }

    public function storeTokenCreds($creds)
    {
        return file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . self::CREDS_FILE, $creds);
    }

    public function hasTokenCreds()
    {
        return file_exists(__DIR__ . DIRECTORY_SEPARATOR . self::CREDS_FILE);
    }

    public function getTokenCreds()
    {
        if (!$this->hasTokenCreds()) {
            header("Location: " . $this->getAuthUrl());
            exit;
        }

        return json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . self::CREDS_FILE), true);
    }

    /**
     * Create a new file with the contents provided in the request.
     *
     * Do not use this to upload a file larger than 150 MB. Instead, create an upload session with upload_session/start.
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-upload
     *
     * @param string $path
     * @param string|resource $contents
     * @param string $mode
     * @param bool $autorename
     *
     * @return array
     */
    public function simpleUpload(string $path, $contents, $mode = 'add', $autorename = true): array
    {
        $arguments = [
            'path' => $path,
            'mode' => $mode,
            'autorename' => $autorename,
        ];

        $response = $this->uploadRequest($arguments, $contents);

        $metadata = json_decode($response->getBody(), true);

        $metadata['.tag'] = 'file';

        return $metadata;
    }

    public function uploadRequest(array $arguments, $body = '')
    {
        if ($this->hasTokenCreds()) {
            $this->refreshTokenCreds();
        }
        $creds = $this->getTokenCreds();

        $headers = [
            'Dropbox-API-Arg' => json_encode($arguments),
            'Authorization' => 'Bearer ' . $creds['access_token'],
        ];

        if ($body !== '') {
            $headers['Content-Type'] = 'application/octet-stream';
        }

        $response = $this->client->post(self::UPLOAD_URL, [
            'headers' => $headers,
            'body' => $body,
        ]);

        return $response;
    }

    public static function getListOfUploadedFiles($season)
    {
        $seasonFolder = __DIR__ . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . 'seasons' . DIRECTORY_SEPARATOR . $season;
        $registryFile = $seasonFolder . DIRECTORY_SEPARATOR . self::DROPBOX_REGISTRY;

        if (file_exists($registryFile)) {
            return json_decode(file_get_contents($registryFile), true);
        }

        return [];
    }

    public static function storeListOfUploadedFiles($season, $list)
    {
        $seasonFolder = __DIR__ . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . 'seasons' . DIRECTORY_SEPARATOR . $season;
        $registryFile = $seasonFolder . DIRECTORY_SEPARATOR . self::DROPBOX_REGISTRY;

        return file_put_contents($registryFile, json_encode($list));
    }

    public static function updateListOfUploadedFiles($season, $metadata)
    {
        $list = self::getListOfUploadedFiles($season);
        $list[$metadata['name']] = $metadata;

        return self::storeListOfUploadedFiles($season, $list);
    }
}
