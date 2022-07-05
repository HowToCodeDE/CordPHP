<?php
    class CordPHP {
        public $botToken;
        public $clientId;
        public $clientSecret;

        public $authorizeURL = 'https://discord.com/api/oauth2/authorize';
        public $tokenURL = 'https://discord.com/api/oauth2/token';
        public $apiURLBase = 'https://discord.com/api/users/@me';
        public $revokeURL = 'https://discord.com/api/oauth2/token/revoke';

        function apiRequest($url, $post=FALSE, $headers=array()) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
          
            $response = curl_exec($ch);
          
          
            if($post)
              curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
          
            $headers[] = 'Accept: application/json';
          
            if($_SESSION['access_token'])
              $headers[] = 'Authorization: Bearer ' . $_SESSION['access_token'];
          
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
          
            $response = curl_exec($ch);
            return json_decode($response);
        }
        
        function setToken($botToken){
            $this->botToken = $botToken;
        }

        function setOAuthCredentials($clientId, $clientSecret){
            $this->clientId = $clientId;
            $this->clientSecret = $clientSecret;
        }

        function authenticate($redirectURI, $scopes = "identify") {
            $params = array(
                'client_id' => $this->clientId,
                'redirect_uri' => $redirectURI,
                'response_type' => 'code',
                'scope' => $scopes
              );
            
              // Redirect the user to Discord's authorization page
              header('Location: https://discord.com/api/oauth2/authorize' . '?' . http_build_query($params));
              die();
        }

        function getToken($redirectURI, $code){
            $token = $this->apiRequest($this->tokenURL, array(
                "grant_type" => "authorization_code",
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'redirect_uri' => $redirectURI,
                'code' => $code
            ));
            //$logout_token = $token->access_token;
            $_SESSION['access_token'] = $token->access_token;

            return $token;
        }

        function getUser(){
            $user = $this->apiRequest($this->apiURLBase);

            return $user;
        }

        function logout(){
            $date = array(
                'token' => $_SESSION['access_token'],
                'token_type_hint' => 'access_token',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            );

            $ch = curl_init($revokeURL);
            curl_setopt_array($ch, array(
                CURLOPT_POST => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                CURLOPT_HTTPHEADER => array('Content-Type: application/x-www-form-urlencoded'),
                CURLOPT_POSTFIELDS => http_build_query($data),
            ));
            
            $response = curl_exec($ch);
            return json_decode($response);
            unset($_SESSION['access_token']);
            header('Location: ' . $_SERVER['PHP_SELF']);
            die();
        }
    }
