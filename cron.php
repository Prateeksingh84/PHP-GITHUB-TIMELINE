<?php
function fetchGithubTimelineHtml() {
    $url = "https://api.github.com/events";

    if (function_exists('curl_init')) {
        // Use cURL if available
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: Mozilla/5.0',
            'Accept: application/vnd.github.v3+json'
        ]);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception("cURL error: " . curl_error($ch));
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode !== 200) {
            throw new Exception("GitHub API responded with HTTP status $httpCode");
        }
    } else {
        // Fallback to file_get_contents with context headers if cURL not available
        $options = [
            "http" => [
                "method" => "GET",
                "header" => "User-Agent: Mozilla/5.0\r\nAccept: application/vnd.github.v3+json\r\n"
            ]
        ];
        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        if ($response === false) {
            throw new Exception("Failed to fetch GitHub timeline with file_get_contents");
        }
        // Check HTTP response code from $http_response_header
        if (isset($http_response_header)) {
            preg_match('#HTTP/\d+\.\d+ (\d+)#', $http_response_header[0], $matches);
            $httpCode = isset($matches[1]) ? (int)$matches[1] : 0;
            if ($httpCode !== 200) {
                throw new Exception("GitHub API responded with HTTP status $httpCode");
            }
        }
    }

    $events = json_decode($response, true);
    if (!$events || !is_array($events)) {
        throw new Exception("Invalid response from GitHub API.");
    }

    $html = "<h2>Latest GitHub Public Events</h2><ul>";
    foreach (array_slice($events, 0, 10) as $event) {
        $type = htmlspecialchars($event['type']);
        $repo = htmlspecialchars($event['repo']['name']);
        $user = htmlspecialchars($event['actor']['login']);
        $html .= "<li><strong>$user</strong> did a <em>$type</em> on <strong>$repo</strong></li>";
    }
    $html .= "</ul>";

    return $html;
}

function sendToRegisteredUsers($htmlContent) {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) {
        echo "No registered users found.\n";
        return;
    }
    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    echo "Emails to send: " . count($emails) . "\n";
    $subject = "GitHub Timeline Update";
    $headers = "From: noreply@example.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    foreach ($emails as $email) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "Sending email to: $email\n";
            $sent = mail($email, $subject, $htmlContent, $headers);
            echo $sent ? "Email sent to $email\n" : "Failed to send email to $email\n";
        } else {
            echo "Invalid email skipped: $email\n";
        }
    }
}

try {
    $html = fetchGithubTimelineHtml();
    sendToRegisteredUsers($html);
    echo "GitHub timeline fetched and emails sent successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
