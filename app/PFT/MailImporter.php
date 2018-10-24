<?php

namespace App\PFT;

use App\Models\Payload;
use Google_Client;
use Google_Service_Gmail;
use Google_Service_Gmail_BatchModifyMessagesRequest;
use Google_Service_Gmail_ListMessagesResponse;
use Exception;
use Illuminate\Console\Command;

class MailImporter
{
    /**
     * @var Google_Client
     */
    protected $google;

    /**
     * @var Google_Service_Gmail
     */
    protected $gmail;

    /**
     * @var Command
     */
    protected $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
        $this->google = $this->getClient();
        $this->gmail = new Google_Service_Gmail($this->google);
    }

    /**
     * Returns an authorized API client.
     * @return Google_Client the authorized client object
     * @throws Exception
     */
    public function getClient()
    {
        $client = new Google_Client();
        $client->setApplicationName('Personal Finance Tracker');
        $client->setScopes([
            Google_Service_Gmail::GMAIL_MODIFY,
            Google_Service_Gmail::GMAIL_LABELS,
        ]);
        $client->setAuthConfig(base_path('credentials.json'));
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        // Load previously authorized token from a file, if it exists.
        $tokenPath = base_path('token.json');
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $authCode = trim(fgets(STDIN));

                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }
            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }
        return $client;
    }

    public function run()
    {
        $messagesData = collect();
        $messageIds = [];

        $messages = $this->fetchMessages();

        if ($messages->count() === 0) {
            $this->command->info("No messages found.");
            return $messages;
        }

        foreach ($messages as $message) {
            $messageIds[] = $message->id;
            $this->command->info("Reading message: {$message->id}");
            $userMessage = $this->gmail->users_messages->get("me", $message->id);
            $payload = $userMessage->getPayload();
            $headers = collect($payload->getHeaders())->keyBy('name');
            $parts = collect($payload->getParts())->keyBy('mimeType');
            $data = [
                'id' => $userMessage->id,
                'labelIds' => $userMessage->labelIds,
                'internalDate' => $userMessage->internalDate,
                'from' => $headers->has('From') ? $headers->get('From')->getValue() : null,
                'to' => $headers->has('To') ? $headers->get('To')->getValue() : null,
                'subject' => $headers->get('Subject')->getValue(),
                'html' => $parts->has('text/html') ? $parts->get('text/html')->getBody()->getData() : null,
                'text' => $parts->has('text/plain') ? $parts->get('text/plain')->getBody()->getData() : null,
            ];
            $this->command->info("Storing message: {$userMessage->id}");
            $messagesData->push($data);
            $this->storePayload($data);
        }

        $this->clearMessages($messageIds);

        return $messagesData;
    }

    protected function fetchMessages() : Google_Service_Gmail_ListMessagesResponse
    {
        return $this->gmail->users_messages->listUsersMessages("me", [
            'labelIds' => [
                "INBOX", // not archived
                "UNREAD", // unread messages
                config('transaction_notifications_label'),
            ]
        ]);
    }

    protected function storePayload($data)
    {
        $payload = new Payload([
            'type' => 'client_email_notification',
            'data' => json_encode($data),
        ]);
        $payload->save();
    }

    protected function clearMessages(array $messageIds)
    {
        $this->command->info("Clearing messages");
        $request = new Google_Service_Gmail_BatchModifyMessagesRequest([
            'ids' => $messageIds,
            'removeLabelIds' => [
                "INBOX", // archive the message
                "UNREAD" // mark the message as read
            ]
        ]);

        $this->gmail->users_messages->batchModify("me", $request);
    }
}
