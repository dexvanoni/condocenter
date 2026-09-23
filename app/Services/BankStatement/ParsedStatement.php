<?php

namespace App\Services\BankStatement;

use Carbon\Carbon;

class ParsedStatement
{
    /**
     * @param  list<array{posted_at: Carbon, amount: float, description: string, fitid: ?string, trn_type: ?string}>  $lines
     */
    public function __construct(
        public readonly array $lines,
        public readonly ?Carbon $periodStart = null,
        public readonly ?Carbon $periodEnd = null,
        public readonly ?float $openingBalance = null,
        public readonly ?float $closingBalance = null,
        public readonly ?array $detectedHeaders = null,
        public readonly bool $needsMapping = false,
    ) {
    }
}
