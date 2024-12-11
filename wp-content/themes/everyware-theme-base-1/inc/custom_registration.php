<?php

global $wpdb;

if ($_POST) {

    $username = esc_sql($_POST['registration_username']);
    $email = esc_sql($_POST['registration_email']);
    $password = esc_sql($_POST['registration_password']);
    $ConfPassword = esc_sql($_POST['registration_confirm_password']);

    $error = array();
    if (strpos($username, ' ') !== FALSE) {
        $error['username_space'] = "Username has a space. Please remove the space and try again.";
    }

    if (preg_match('/[^a-zA-Z0-9_\.\-@]/', $username) == 1){
        $error['username_specialcharacters'] = "Username has incorrect special characters.";
    }
    if (strlen($username) > 25) {
        $error['username_limit'] = "Username has passed character limit of 25 characters.";
    }
    if (empty($username)) {
        $error['username_empty'] = "Username is empty. Please add a Username and try again.";
    }

    if (username_exists($username)) {
        $error['username_exists'] = "Username already exists.";
    }

    if (!is_email($email)) {
        $error['email_valid'] = "Email address does not follow correct email format. Please fix and try again.";
    }

    if (email_exists($email)) {
        $error['email_existence'] = "An account already exists with this email address. Log in to the account associated with this email address or use a different one.";
    }

    if (strlen($password) <= '7') {
        $error['password_length'] = "Password must be at least 8 Characters.";
    }
    if (!preg_match("#[0-9]+#",$password)) {
        $error['password_number'] = "Password must contain at least 1 Number.";
    }
    if (!preg_match("#[A-Z]+#",$password)) {
        $error['password_capital'] = "Password must contain at least 1 Capital letter.";
    }
    if (!preg_match("#[a-z]+#",$password)) {
        $error['password_lowercase'] = "Password must contain at least 1 Lowercase letter.";
    }
    if (strpos($password, ' ') !== FALSE) {
        $error['password_space'] = "Password has a space. Please remove the space and try again.";
    }
    if (strcmp($password, $ConfPassword) !== 0) {
        $error['password'] = "Passwords didn't match. Please try again and ensure passwords match.";
    }

    if (count($error) == 0) {

        wp_create_user($username, $password, $email);
        echo '<div class="registration_success">';
        echo 'User Created Successfully. Please login <a href="/form-login/">here</a> or using the login link in the top bar.';
        echo '</div>';
        exit();
    } else {

        echo '<div class="registration_errors">';
        echo '<div class="registration_error_heading">Registration Error</div>';
        echo '<ul>';
        foreach ($error as $error_message) {
            echo '<li class="registration_error_message">' . $error_message . '</li>';
        }
        echo '</ul>';
        echo '</div>';

    }
}