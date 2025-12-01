<?php
class User
{
    # user infox    
    private $first_name;
    private $middle_name;
    private $last_name;

    # account info
    private $email;
    private $role;
    private $department;
    private $profile_image;
    private $username;
    private $password;

    function __construct($firstName, $lastName, $userRole, $userNickname, $userDepartment, $userEmail)
    {
        $this->first_name = $firstName;
        $this->last_name = $lastName;
        $this->role = $userRole;
        $this->username = $userNickname;
        $this->department = $userDepartment;
        $this->email = $userEmail;
    }

    public function getFirstName()
    {
        return $this->first_name;
    }

    public function getMiddleName()
    {
        return $this->middle_name;
    }

    public function getLastName()
    {
        return $this->last_name;
    }

    public function getEmail()
    {
        return $this->email;
    }


    public function getRole()
    {
        return $this->role;
    }

    public function getDepartment()
    {
        return $this->department;
    }

    public function getProfileImage()
    {
        return $this->profile_image;
    }
    public function setProfileImage($imagePath)
    {
        $this->profile_image = $imagePath;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($newUsername)
    {
        $this->username = $newUsername;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($newPassword)
    {
        $this->password = $newPassword;
    }
}
