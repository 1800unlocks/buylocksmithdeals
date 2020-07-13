<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
    require  BUYLOCKSMITH_DEALS_DIR.'/assets/twilio-php-master/src/Twilio/autoload.php';
    use Twilio\Rest\Client;
	$sid = get_option('twillo_sid');
    $token = get_option('twillo_token');
    $twillo_phone_number = get_option('twillo_phone_number');
    $client = new Client($sid, $token);
    // Use the client to do fun stuff like send text messages!
    try {
        $message = $client->messages->create(
            // the number you'd like to send the message to
            $phone_no,
            array(
                // A Twilio phone number you purchased at twilio.com/console
                'from' => $twillo_phone_number,
                // the body of the text message you'd like to send
                'body' => $message_body
            )
        );  
        if($message->sid !=''){
            echo 'Message send to: '.$phone_no;
        }
    } catch (\Twilio\Exceptions\TwilioException $e) {
           $code= $e->getStatusCode();
           $content = $e->getMessage();
           $find_str='[HTTP '.$code.'] Unable to create record:';
           $content= str_replace($find_str,"",$content);
           echo $content;
           
    }
    
    