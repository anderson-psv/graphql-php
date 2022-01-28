<?php

namespace App;

interface iModel
{
    public function setData(array $data);
    public function getData(int $id);
    public function save();
}
