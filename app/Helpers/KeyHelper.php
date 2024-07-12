<?php

namespace App\Helpers;


class KeyHelper
{

    /**
     * Generates a unique ID for the database
     * @param array $data
     * @return string
     */
    public static function generateUuid8()
    {

        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $uuid = '';
        for ($i = 0; $i < 8; $i++) {
            $uuid .= $chars[mt_rand(0, 61)];
        }
        return $uuid;

    }

}