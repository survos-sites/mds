<?php

namespace App\Message;

final class ExtractMessage
{

    // token, 10 records at a time
     public function __construct(
         public readonly string $grpCode,
         public readonly string $tokenCode, // used for lookup
         public readonly array $data,
     ) {
     }
}
