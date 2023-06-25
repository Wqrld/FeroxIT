<?php

function curlPost($url, $data = NULL, $headers = []) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); //timeout in seconds
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_ENCODING, 'identity');

    
    if (!empty($data)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }

    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $response = curl_exec($ch);
    if (curl_error($ch)) {
        trigger_error('Curl Error:' . curl_error($ch));
    }

    curl_close($ch);
    return $response;
}

function getPost()
{
    if(!empty($_POST))
    {
        // when using application/x-www-form-urlencoded or multipart/form-data as the HTTP Content-Type in the request
        // NOTE: if this is the case and $_POST is empty, check the variables_order in php.ini! - it must contain the letter P
        return $_POST; // $_POST['naam'], email, message
    }

    return [];
}

// Import the Postmark Client Class:
require_once('./vendor/autoload.php');
use Postmark\PostmarkClient;
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// echo($_POST['g-recaptcha-response']);
$response=curlPost("https://www.google.com/recaptcha/api/siteverify?secret=" . $_ENV['CAPTCHA_SECRET'] . "&response=" . $_POST['g-recaptcha-response'], [], []);
if(json_decode($response, true)['success'] != true){
    die("Captcha verification failed");
}

// echo('Uw bericht is verzonden.');
if(empty($_POST)){
    die("empty POST");
}

if($_POST['email'] != "fakeemail@gmail.com") {
    die("Your browser pre-filled our anti-spam holder. Please try another browser or contact us through other means.");
}



$client = new PostmarkClient($_ENV['POSTMARK_KEY']);
$fromEmail = "contactform@feroxhosting.nl";
$toEmail = "feroxitcontact@feroxit.nl";
$subject = "Contact form response";
$htmlBody = "From: " . $_POST['naam'] . " (" . $_POST["mal"] . ")<br /> Message: <br />" . $_POST['bericht'];
$textBody = "From: " . $_POST['naam'] . " (" . $_POST["mal"] . ")<br /> Message: <br />" . $_POST['bericht'];
$tag = "contact-form";
$trackOpens = true;
$trackLinks = "None";
$messageStream = "contact-form";

// Send an email:
$sendResult = $client->sendEmail(
  $fromEmail,
  $toEmail,
  $subject,
  $htmlBody,
  $textBody,
  $tag,
  $trackOpens,
  $_POST["mal"], // Reply To
  NULL, // CC
  NULL, // BCC
  NULL, // Header array
  NULL, // Attachment array
  $trackLinks,
  NULL, // Metadata array
  $messageStream
);

echo("Bericht verzonden. U zal binnenkort een e-mail terug van ons ontvangen.");
