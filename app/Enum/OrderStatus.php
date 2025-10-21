<?php

namespace App\Enum;

enum OrderStatus : string
{
    const PENDING = 'pending';
    const CONFIRMED = 'confirmed';
    const PROCESSING = 'processing';
    const SHIPPED = 'shipped';
    const DELIVERED = 'delivered';
    const CANCELED = 'canceled';
    public static function map() : array
    {
        return [
            self::PENDING    => 'yellow',
            self::CONFIRMED  => 'blue',
            self::PROCESSING => 'blue',
            self::SHIPPED    => 'orange',
            self::DELIVERED  => 'green',
            self::CANCELED   => 'red',
        ];
    }
}
