<?php

namespace App\Services\Interfaces;


interface DsrServiceInterface
{
    public function create(array $data);
    public function getAll();
    public function getById(int $id);
    public function update(int $id, array $data);
    public function send(array $data);
}