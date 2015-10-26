<?php
namespace EnglishDomHybridAuthOd\Entity\Odnoklassniki;

use Hybridauth\Http\Request;

class Profile extends \Hybridauth\Entity\Profile
{
    function getPhotoURL($width = 150, $height = 150)
    {
        return '';
    }

    function getProfileURL()
    {
        return isset($this->profileURL) ?
                $this->profileURL :
                ('http://ok.ru/profile/' . $this->getIdentifier());
    }

    function postEvent(Event &$event)
    {
        return false;
    }

    public static function generateFromResponse($response, $adapter)
    {
        $profile = parent::generateFromResponse($response, $adapter);

        $uid = static::parser('uid', $response);
        $profileUrl = $uid
            ? 'http://ok.ru/profile/' . $uid
            : '';

        $bDate = static::parser('birthday', $response);
        $firstName = static::parser('first_name', $response);
        $lastName = static::parser('last_name', $response);


        $profile->setIdentifier($uid);
        $profile->setFirstName($firstName);
        $profile->setLastName($lastName);
        $profile->setDisplayName($firstName . ' ' . $lastName);
        $profile->setProfileURL($profileUrl);
        if (isset($bDate)) {
            $buf = explode('-', $bDate);
            if (isset($buf[2])) {
                $profile->setBirthDay(sprintf("%'.02d", $buf[2]));
            }
            if (isset($buf[1])) {
                $profile->setBirthMonth(sprintf("%'.02d", $buf[1]));
            }
            if (isset($buf[0])) {
                $profile->setBirthYear($buf[0]);
            }
        }


        $sex = static::parser('gender', $response);

        $profile->setGender($sex);

        return $profile;
    }
}
