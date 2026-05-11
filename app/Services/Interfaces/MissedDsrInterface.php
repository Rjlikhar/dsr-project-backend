<?php

namespace App\Services\Interfaces;

interface MissedDsrInterface{
     public function store($userId, array $data);
     public function getAllMissedDsrRequests();
     public function updateStatus( $req, $id);
     public function getPendingNotifications(int $userId);
}

?>