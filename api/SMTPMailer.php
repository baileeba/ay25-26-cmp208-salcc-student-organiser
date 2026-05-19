<?php
class SMTPMailer {
    private $host;
    private $port;
    private $username;
    private $password;
    private $sender_email;
    private $sender_name;
    private $socket = null;
    
    public function __construct($host, $port, $username, $password, $sender_email, $sender_name) {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->sender_email = $sender_email;
        $this->sender_name = $sender_name;
    }
    
    public function sendEmail($to, $subject, $message, $isHtml = true) {
        try {
            $this->socket = @fsockopen('ssl://' . $this->host, $this->port, $errno, $errstr, 10);
            
            if (!$this->socket) {
                error_log("SMTP Connection failed: $errstr ($errno)");
                return false;
            }
            
            
            $response = $this->readResponse();
            error_log("SMTP: " . $response);
            
            
            $this->sendCommand("EHLO localhost");
            
            
            $this->sendCommand("STARTTLS");
            
            
            stream_context_set_option($this->socket, 'ssl', 'allow_self_signed', true);
            stream_context_set_option($this->socket, 'ssl', 'verify_peer', false);
            
            if (!stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                error_log("SMTP TLS negotiation failed");
                return false;
            }
            
            
            $this->sendCommand("EHLO localhost");
            
            
            $this->sendCommand("AUTH LOGIN");
            $this->sendCommand(base64_encode($this->username));
            $this->sendCommand(base64_encode($this->password));
            
            
            $this->sendCommand("MAIL FROM: <" . $this->sender_email . ">");
            $this->sendCommand("RCPT TO: <" . $to . ">");
            $this->sendCommand("DATA");
            
            
            $headers = "From: " . $this->sender_name . " <" . $this->sender_email . ">\r\n";
            $headers .= "To: " . $to . "\r\n";
            $headers .= "Subject: " . $subject . "\r\n";
            $headers .= "Date: " . date('r') . "\r\n";
            
            if ($isHtml) {
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            } else {
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            }
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "X-Mailer: StudSort/1.0\r\n";
            
            // Send headers and message
            fwrite($this->socket, $headers . "\r\n" . $message . "\r\n.\r\n");
            $response = $this->readResponse();
            error_log("SMTP: " . $response);
            
            // Close connection
            $this->sendCommand("QUIT");
            fclose($this->socket);
            
            error_log("Email sent successfully to $to");
            return true;
            
        } catch (Exception $e) {
            error_log("SMTP Error: " . $e->getMessage());
            if ($this->socket) {
                fclose($this->socket);
            }
            return false;
        }
    }
    
    private function sendCommand($command) {
        error_log("SMTP Command: $command");
        fwrite($this->socket, $command . "\r\n");
        $response = $this->readResponse();
        error_log("SMTP Response: $response");
        return $response;
    }
    
    private function readResponse() {
        $response = '';
        while ($line = fgets($this->socket, 1024)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }
        return trim($response);
    }
}
?>
