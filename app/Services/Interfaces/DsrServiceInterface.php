<?php

namespace App\Services\Interfaces;


interface DsrServiceInterface
{
    public function create(array $data);
    public function getAll();
    // public function getById(int $id);
    public function send(array $data);
    public function getDraftByUser(int $userId);
}