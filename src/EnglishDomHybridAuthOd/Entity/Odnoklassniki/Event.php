<?php
namespace EnglishDomHybridAuthOd\Entity\Odnoklassniki;

use \Hybridauth\Http\Request;

class Event extends \Hybridauth\Entity\Entity
{
    protected $date = null;
    protected $name = null;
    protected $description = null;
    protected $start_time = null;
    protected $end_time = null;
    protected $location = null;
    protected $street = null;
    protected $zip = null;
    protected $country = null;
    protected $latitude = null;
    protected $longitude = null;
    protected $ticketURI = null;

    function getDate() {
        return $this->date;
    }

    function getName() {
        return $this->name;
    }

    function getDescription() {
        return $this->description;
    }

    function getStartTime() {
        return $this->start_time;
    }

    function getEndTime() {
        return $this->end_time;
    }

    function getLocation() {
        return $this->location;
    }

    function getStreet() {
        return $this->street;
    }

    function getZip() {
        return $this->zip;
    }

    function getCountry() {
        return $this->country;
    }

    function getLatitude() {
        return $this->latitude;
    }

    function getLongitude() {
        return $this->longitude;
    }

    function getTicketURI() {
        return $this->ticketURI;
    }

    function setDate($date) {
        $this->date = $date;
    }

    function setName($name) {
        $this->name = $name;
    }

    function setDescription($description) {
        $this->description = $description;
    }

    function setStartTime($start_time) {
        $this->start_time = static::formatDate($start_time);
    }

    function setEndTime($end_time) {
        $this->end_time = static::formatDate($end_time);
    }

    function setLocation($location) {
        $this->location = $location;
    }

    function setStreet($street) {
        $this->street = $street;
    }

    function setZip($zip) {
        $this->zip = $zip;
    }

    function setCountry($country) {
        $this->country = $country;
    }

    function setLatitude($latitude) {
        $this->latitude = $latitude;
    }

    function setLongitude($longitude) {
        $this->longitude = $longitude;
    }

    function setTicketURI($ticketURI) {
        $this->ticketURI = $ticketURI;
    }

    function delete() {
        $identifier = $this->getIdentifier();
        if(empty($identifier)) return true;
        $response = $this->getAdapter()->signedRequest('/'.$identifier,Request::DELETE);
        $response = json_decode($response);
        if(isset($response->error) || $response === false) return false;
        $this->setIdentifier(null);
        return true;
    }

    private static function formatDate($date) {
        if(is_null($date)) return null;
        if(is_string($date))
        {
            $date = strtotime($date);
        }
        return date(\DateTime::ISO8601, $date);
    }

    public static function generateFromResponse($response,$adapter) {
        $event = parent::generateFromResponse($response,$adapter);
        $event->setIdentifier  ( static::parser( 'id',$response )          );
        $event->setName        ( static::parser( 'name',$response )        );
        $event->setDescription ( static::parser( 'description',$response ) );
        $event->setStartTime   ( static::parser( 'start_time',$response  ) );
        $event->setEndTime     ( static::parser( 'end_time',$response )    );
        $event->setLocation    ( static::parser( 'location',$response )    );
        $event->setTicketURI   ( static::parser( 'ticket_uri',$response )  );
        if(property_exists($response, 'venue')) {
            $venue = $response->venue;
            $event->setStreet    ( static::parser( 'street',$venue )    );
            $event->setZip       ( static::parser( 'zip',$venue )       );
            $event->setCountry   ( static::parser( 'country',$venue )   );
            $event->setLatitude  ( static::parser( 'latitude',$venue )  );
            $event->setLongitude ( static::parser( 'longitude',$venue ) );
        }
        return $event;
    }
}
