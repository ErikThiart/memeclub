<?php

class User {

    public $userID, $username;
    public $avatar;
    public $profileViews, $memeImpressions, $memeCount;

    function __construct() {
            
    }

    /**
     * Initialize a single user based on the PDO statement SELECT * FROM users. 
     *
     * @param array $userResult 
     * @return void
     */
    public function initUserFromAll($userResult, $pdo) {
        $this->userID       = $userResult['userID'];
        $this->username     = $userResult['username'];
        $this->avatar       = !empty($userResult['avatar']) ? $userResult['avatar'] : 'uploads/avatars/default.jpg';

        $this->profileViews             = $this->getProfileViews($this->userID, $pdo);
        $this->memeCount                = $this->getMemeCount($this->userID, $pdo);
        $this->memeImpressions          = $this->getMemeImpressions($this->userID, $pdo);
    }
    
    /**
     * Register a user
     *
     * @param [string] $username
     * @param [string] $email
     * @param [string] $password_hash
     * @param [int] $referrer_id
     * @return void
     */
    public static function register($username, $email, $password_hash, $referrer_id) {
        // get the DB
        global $pdo;
        $app_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}";
        // create the token
        $token = bin2hex(random_bytes(3).$email.time().random_bytes(1));
        // create invite code
        $invite_code = substr(md5($email.microtime()),rand(0,26),10); //TODO: MD5 is completely insecure. use password_hash.
        // register the user
        try {
            $stmt = $pdo->prepare(
            'INSERT INTO users (username, email, password_hash, activation_token, invite_code) 
                VALUES (:username, :email, :password, :token, :invite_code)');
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password_hash);
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':invite_code', $invite_code);
            $stmt->execute();
            $userID = $pdo->lastInsertId();
            // send off a registraion email
            $message = '
            Please confirm your registration click on the link below: <br>
            '.$app_url.'/confirm_registration.php?activation_code='.$token.'&email='.$email.' <br>
            ';
            $subject = "Confirm your registration.";

            if (sendmail($email,$subject,$message,$username)) {
                //log the referral
                if (!empty($referrer_id)) {
                    $stmt = $pdo->prepare('INSERT INTO referrals (referral_id, referrer_id) VALUES (:referral_id, :referrer_id)');
                    $stmt->bindParam(':referral_id', $userID);
                    $stmt->bindParam(':referrer_id', $referrer_id);
                    if($stmt->execute()) {
                    //get the email of the referer
                    $stmt = $pdo->prepare(
                        'SELECT email, username 
                        FROM users 
                        WHERE id = :referrer_id');
                    $stmt->bindParam(':referrer_id', $referrer_id);
                    $stmt->execute();
                    $referrer = $stmt->fetch();
                    $message = '
                    <h3>Congratulations, '.$referrer['username'].'</h3>
                    <p>'.$username.' just signed up to Memeclub using your invite code!</p>
                    <p>Invite more people and become the top memester.</p>
                    ';
                    $subject = 'Someone redeemed your invite code on MemeClub';
                    sendmail($referrer['email'],$subject,$message,$referrer['username']);
                    }
                }
                // Redirect the user to success page.
                $_SESSION['heading'] = "Registration Successful";
                $_SESSION['message'] = "Thank you for registering on our platform. We have send you a confirmation E-mail which you need to accept before your registration is completed.<br> Please check your spam folder if you use gmail.";
                redirect('success.php');
            } 
            
            $_SESSION['message'] = "Confirmation E-mail was not sent.";
            $_SESSION['heading'] = "Something went wrong.";
            redirect('error.php');

        } catch(PDOException $e) {
            $_SESSION['message'] = "Error: " . $e->getMessage();
            $_SESSION['heading'] = "Something went wrong.";
            redirect('error.php');     
        }
    //TODO: Both end of try and end of catch end in redirect. Move to here instead.
    }

    function confirmRegistration($token, $email) {
        // get the DB
        global $pdo;
        //confirm user's registration
        $stmt = $pdo->prepare('SELECT id FROM users WHERE activation_token = :token AND email = :email AND activated = 0');
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch();
        if($user) {
            //make the user active and redirect to success page
            $stmt = $pdo->prepare('UPDATE users SET activated = 1 WHERE id = :user_id');
            $stmt->bindParam(':user_id', $user['id']);
            if($stmt->execute()){
            $_SESSION['heading'] = "Account Activated";
            $_SESSION['message'] = "Thank you, you can now log in.";
            redirect('success.php');    
            }
            $heading = "Something went wrong (Error 1)";
            $message = "Please contact support on info@memeclub.xyz and tell them the error code you received.";
            redirect('error.php');   
        } 
        // else { //else is not needed as if already redirects. Default behavior.
        $_SESSION['heading'] = "Invalid Token OR You have already been activated.";
        $_SESSION['message'] = "Please contact support on info@memeclub.xyz and we can investigate and activate you manually.";
        redirect('error.php');   
        // }
    }

    function resetPassword($password, $userID, $email, $reset_token) {
        // get the DB
        global $pdo;
        $stmt = $pdo->prepare('UPDATE users SET password_hash = :password, password_reset_token = NULL WHERE id = :user_id AND email = :email AND password_reset_token = :reset_token');
        $stmt->bindParam(':password', password_hash($password, PASSWORD_DEFAULT));
        $stmt->bindParam(':user_id', $userID);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':reset_token', $reset_token);
        if($stmt->execute()) {
            redirect('index.php');
        } 

        $_SESSION['message'] = "Password Reset Failed";
        $_SESSION['heading'] = "Contact Support";
        redirect('error.php'); 
    }

    function getMemeCount($userID, $pdo) {
        $stmt = $pdo->prepare('SELECT COUNT(id) AS total FROM memes WHERE user_id = :profile');
        $stmt->bindParam(':profile', $userID);
        $stmt->execute();
        return $stmt->Fetch()['total'];
    }

    function getProfileViews($userID, $pdo) {
        $stmt = $pdo->prepare('SELECT COUNT(id) AS total FROM user_impressions WHERE profile_id = :profile');
        $stmt->bindParam(':profile', $userID);
        $stmt->execute();
        return $stmt->Fetch()['totalS'];
    }

    /**
     * Gets a user's meme impresssions
     *
     * @param string $userID
     * @param PHPDataObject $pdo as defined in the init.php file. 
     * @return int count for meme impressions.
     */
    function getMemeImpressions($userID, $pdo) {
        $stmt = $pdo->prepare('SELECT COUNT(meme_impressions.id) AS total FROM `meme_impressions` INNER JOIN memes ON meme_impressions.meme_name = memes.name INNER JOIN users ON memes.user_id = users.id WHERE users.id = :profile');
        $stmt->bindParam(':profile', $userID);
        $stmt->execute();
        return $stmt->Fetch()['total'];
    }

}