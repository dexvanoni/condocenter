<?php

namespace Tests\Unit;

use App\Services\BankStatement\CsvStatementParser;
use App\Services\BankStatement\OfxStatementParser;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BankStatementParserTest extends TestCase
{
    public function test_ofx_parser_reads_transactions_and_ledger_balance(): void
    {
        $ofx = <<<'OFX'
OFXHEADER:100
DATA:OFXSGML
VERSION:102

<OFX>
<BANKMSGSRSV1>
<STMTTRNRS>
<STMTRS>
<BANKTRANLIST>
<STMTTRN>
<TRNTYPE>CREDIT
<DTPOSTED>20260915
<TRNAMT>150.00
<FITID>ABC123
<MEMO>Taxa condominial
</STMTTRN>
<STMTTRN>
<TRNTYPE>DEBIT
<DTPOSTED>20260916
<TRNAMT>-45.50
<FITID>DEF456
<NAME>Tarifa bancaria
</STMTTRN>
</BANKTRANLIST>
<LEDGERBAL>
<BALAMT>1200.25
<DTASOF>20260916
</LEDGERBAL>
</STMTRS>
</STMTTRNRS>
</BANKMSGSRSV1>
</OFX>
OFX;

        $parsed = app(OfxStatementParser::class)->parse($ofx);

        $this->assertCount(2, $parsed->lines);
        $this->assertSame(150.0, $parsed->lines[0]['amount']);
        $this->assertSame('ABC123', $parsed->lines[0]['fitid']);
        $this->assertSame(-45.5, $parsed->lines[1]['amount']);
        $this->assertSame(1200.25, $parsed->closingBalance);
        $this->assertSame('2026-09-15', $parsed->periodStart->toDateString());
        $this->assertSame('2026-09-16', $parsed->periodEnd->toDateString());
    }

    public function test_csv_parser_with_semicolon_and_signed_amount(): void
    {
        $csv = "Data;Descrição;Valor\n15/09/2026;Recebimento PIX;1.250,00\n16/09/2026;Pagamento limpeza;-320,50\n";

        $parsed = app(CsvStatementParser::class)->parse($csv);

        $this->assertFalse($parsed->needsMapping);
        $this->assertCount(2, $parsed->lines);
        $this->assertSame(1250.0, $parsed->lines[0]['amount']);
        $this->assertSame(-320.5, $parsed->lines[1]['amount']);
    }

    public function test_csv_parser_with_debit_credit_columns(): void
    {
        $csv = "date,description,debit,credit\n2026-09-10,Tarifa,12.30,\n2026-09-11,Deposito,,500.00\n";

        $parsed = app(CsvStatementParser::class)->parse($csv);

        $this->assertCount(2, $parsed->lines);
        $this->assertSame(-12.3, $parsed->lines[0]['amount']);
        $this->assertSame(500.0, $parsed->lines[1]['amount']);
    }

    public function test_csv_unknown_headers_request_mapping(): void
    {
        $csv = "col_a,col_b,col_c\n2026-09-10,Foo,10\n";

        $parsed = app(CsvStatementParser::class)->parse($csv);

        // Fallback posicional (3 colunas) ainda funciona
        $this->assertFalse($parsed->needsMapping);
        $this->assertCount(1, $parsed->lines);

        $csv2 = "foo,bar\n2026-09-10,10\n";
        $parsed2 = app(CsvStatementParser::class)->parse($csv2);
        $this->assertTrue($parsed2->needsMapping);
    }

    public function test_ofx_without_transactions_fails(): void
    {
        $this->expectException(ValidationException::class);
        app(OfxStatementParser::class)->parse('<OFX></OFX>');
    }
}
