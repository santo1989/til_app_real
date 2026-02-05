<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateObjectivesStatusEnum extends Migration
{
    public function up()
    {
        // Drop any existing check constraint on objectives.status and add a named constraint
        $sql = <<<'SQL'
DECLARE @cname nvarchar(200);
SELECT @cname = tc.CONSTRAINT_NAME
FROM INFORMATION_SCHEMA.CHECK_CONSTRAINTS cc
JOIN INFORMATION_SCHEMA.CONSTRAINT_COLUMN_USAGE ccu ON cc.CONSTRAINT_NAME = ccu.CONSTRAINT_NAME
JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS tc ON cc.CONSTRAINT_NAME = tc.CONSTRAINT_NAME
WHERE ccu.TABLE_NAME = 'objectives' AND ccu.COLUMN_NAME = 'status';

IF @cname IS NOT NULL
BEGIN
    EXEC('ALTER TABLE [dbo].[objectives] DROP CONSTRAINT [' + @cname + ']');
END

-- Add named constraint allowing pending and rejected in addition to existing values
ALTER TABLE [dbo].[objectives]
ADD CONSTRAINT CK_objectives_status_allowed CHECK ([status] IN ('draft','pending','set','revised','rejected','dropped'));
SQL;

        DB::unprepared($sql);
    }

    public function down()
    {
        // Remove our named constraint and restore the original allowed set (without pending/rejected)
        $sql = <<<'SQL'
IF EXISTS (SELECT 1 FROM sys.check_constraints WHERE name = 'CK_objectives_status_allowed')
BEGIN
    ALTER TABLE [dbo].[objectives] DROP CONSTRAINT CK_objectives_status_allowed;
END

ALTER TABLE [dbo].[objectives]
ADD CONSTRAINT CK_objectives_status_allowed_orig CHECK ([status] IN ('draft','set','revised','dropped'));
SQL;

        DB::unprepared($sql);
    }
}
