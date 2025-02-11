<?php

namespace App\Message;

final class Extract
{

    // url to 10 records
     public function __construct(
         public readonly string $url,
     ) {
     }
}
