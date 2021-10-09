<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class RegisterNewUserMail extends Mailable
{
    public function __construct( $username, $role, $email, $password ) {
        $this->username = $username;
        $this->role = $role;
        $this->email = $email;
        $this->password = $password;
    }

    public function build() {
        return $this->subject('Добро пожаловать в youngeek!')->text('emails.register-new-user', [
            'username' => $this->username,
            'role'     => $this->role,
            'email'    => $this->email,
            'password' => $this->password,
        ]);
    }
}
