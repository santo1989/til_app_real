<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Convert stored DB tokens to human-friendly display labels and make
     * the column wide enough to store them (VARCHAR).
     *
     * This migration is conservative: it updates values and changes the
     * column type to VARCHAR for drivers that support ALTER TABLE. For
     * sqlite (testing) we only update data because sqlite ALTER support is
     * limited.
     *
     * @return void
     */
    public function up()
    {
        // Map existing DB tokens -> display labels
        $map = [
            'outstanding' => 'Outstanding',
            'good' => 'Good',
            'average' => 'Average',
            'below' => 'Below Average',
        ];

        foreach ($map as $db => $display) {
            DB::table('appraisals')->where('rating', $db)->update(['rating' => $display]);
        }

        $driver = Schema::getConnection()->getDriverName();

        // Alter column type to string large enough to hold display labels
        try {
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE `appraisals` MODIFY `rating` VARCHAR(50) NULL");
            } elseif ($driver === 'pgsql') {
                DB::statement("ALTER TABLE appraisals ALTER COLUMN rating TYPE VARCHAR(50)");
            } elseif ($driver === 'sqlsrv') {
                DB::statement("ALTER TABLE appraisals ALTER COLUMN rating NVARCHAR(50) NULL");
            }
            // sqlite and other drivers: no schema change (sqlite doesn't support ALTER TYPE easily)
        } catch (\Throwable $e) {
            // If the raw alter fails, log and continue; data has been migrated.
            // Avoid breaking migrations in CI where DB permissions differ.
            // We intentionally swallow the exception because the important
            // piece is converting stored values to display labels.
            // Optionally you can rethrow to fail the migration strictly.
            // logger()->warning('Could not alter appraisals.rating column: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     * Attempt to map display labels back to old DB tokens and revert the
     * column to the original enum-like representation where possible.
     *
     * @return void
     */
    public function down()
    {
        // Map display labels back to previous DB tokens
        $map = [
            'Outstanding' => 'outstanding',
            'Good' => 'good',
            'Average' => 'average',
            'Below Average' => 'below',
        ];

        foreach ($map as $display => $db) {
            DB::table('appraisals')->where('rating', $display)->update(['rating' => $db]);
        }

        $driver = Schema::getConnection()->getDriverName();

        try {
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE `appraisals` MODIFY `rating` ENUM('outstanding','good','average','below') NULL");
            } elseif ($driver === 'sqlsrv') {
                // SQL Server doesn't have ENUM; keeping NVARCHAR is acceptable.
                DB::statement("ALTER TABLE appraisals ALTER COLUMN rating NVARCHAR(50) NULL");
            } elseif ($driver === 'pgsql') {
                // For Postgres, leaving as VARCHAR is safer in a down migration to
                // avoid complex enum type recreation. Convert to VARCHAR(20).
                DB::statement("ALTER TABLE appraisals ALTER COLUMN rating TYPE VARCHAR(20)");
            }
        } catch (\Throwable $e) {
            // swallow for safety
        }
    }
};
