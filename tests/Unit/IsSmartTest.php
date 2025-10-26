<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Rules\IsSmart;

class IsSmartTest extends TestCase
{
    public function test_detects_numeric_measure()
    {
        $rule = new IsSmart();
        $this->assertTrue($rule->passes('target', '14 days'), 'Numeric with unit should be SMART');
        $this->assertTrue($rule->passes('target', 'Reduce to 10'), 'Comparative numeric should be SMART');
    }

    public function test_detects_percentage_as_smart()
    {
        $rule = new IsSmart();
        $this->assertTrue($rule->passes('target', '90%'), 'Percentage should be SMART');
        $this->assertTrue($rule->passes('target', '>= 85 %'), 'Percentage with operator should be SMART');
    }

    public function test_rejects_vague_target()
    {
        $rule = new IsSmart();
        $this->assertFalse($rule->passes('target', 'Improve customer satisfaction'), 'Vague target without numeric should be rejected');
    }
}
