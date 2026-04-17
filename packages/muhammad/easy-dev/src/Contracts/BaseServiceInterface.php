<?php

namespace EasyDev\Laravel\Contracts;

interface BaseServiceInterface
{
    public function all();
    public function create(array $data);
    public function update($model, array $data);
    public function delete($model);
    public function find($id);
}
