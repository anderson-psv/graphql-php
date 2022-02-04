<?php

namespace App;

interface iModel
{
    public function setData(array $data);
    public function asArray();
    public function save();
    public function delete();
}
