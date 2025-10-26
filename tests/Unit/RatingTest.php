<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Support\Rating;

class RatingTest extends TestCase
{
    public function test_to_db_token_with_exact_labels()
    {
        $this->assertEquals('outstanding', Rating::toDbToken('Outstanding'));
        $this->assertEquals('good', Rating::toDbToken('Good'));
        $this->assertEquals('average', Rating::toDbToken('Average'));
        $this->assertEquals('below', Rating::toDbToken('Below Average'));
    }

    public function test_to_db_token_is_case_insensitive_and_accepts_legacy_keys()
    {
        $this->assertEquals('good', Rating::toDbToken('excellent'));
        $this->assertEquals('good', Rating::toDbToken('Excellent'));
        $this->assertEquals('below', Rating::toDbToken('below'));
    }

    public function test_to_db_token_falls_back_to_default_for_unknown()
    {
        $default = config('rating.default_db_token', 'average');
        $this->assertEquals($default, Rating::toDbToken('something random'));
    }

    public function test_to_display_label()
    {
        $this->assertEquals('Good', Rating::toDisplayLabel('good'));
        $this->assertEquals('Below Average', Rating::toDisplayLabel('below'));
        // Unknown token returns ucfirst fallback
        $this->assertEquals('Custom', Rating::toDisplayLabel('custom'));
    }
}
