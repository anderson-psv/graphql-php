<?php

namespace App;

interface iModel
{
    public function setData(array $data);
    public function getData();
    public function save();
    public function delete();
}
