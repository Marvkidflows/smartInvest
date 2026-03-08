<?php
// database/migrations/2024_03_03_000001_add_registration_fields_to_users.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Stage 1: Basic Information
            $table->string('full_name')->nullable()->after('name');
            $table->string('country_code', 10)->nullable()->after('email'); // +234, +1, etc
            $table->string('phone', 20)->nullable()->after('country_code');
            $table->string('country', 3)->nullable()->after('phone'); // NG, US, GB
            $table->string('referral_code')->nullable()->after('country');
            $table->boolean('terms_accepted')->default(false)->after('referral_code');
            $table->boolean('risk_accepted')->default(false)->after('terms_accepted');
            
            // Stage 2: KYC Verification
            $table->string('id_type')->nullable()->after('risk_accepted'); // passport, national_id, drivers_license
            $table->string('id_number')->nullable()->after('id_type');
            $table->string('id_document_path')->nullable()->after('id_number');
            $table->string('selfie_path')->nullable()->after('id_document_path');
            $table->date('date_of_birth')->nullable()->after('selfie_path');
            $table->text('residential_address')->nullable()->after('date_of_birth');
            $table->string('city')->nullable()->after('residential_address');
            $table->string('state')->nullable()->after('city');
            $table->string('postal_code')->nullable()->after('state');
            
            // Stage 3: Investor Profile
            $table->string('employment_status')->nullable()->after('postal_code'); // employed, self_employed, unemployed, retired
            $table->string('annual_income_range')->nullable()->after('employment_status'); // <25k, 25k-50k, etc
            $table->string('source_of_funds')->nullable()->after('annual_income_range'); // salary, business, etc
            $table->string('investment_experience')->nullable()->after('source_of_funds'); // none, beginner, intermediate, expert
            $table->string('risk_tolerance')->nullable()->after('investment_experience'); // low, medium, high
            $table->text('investment_objectives')->nullable()->after('risk_tolerance');
            
            // Stage 4: Security Setup
            $table->boolean('two_factor_enabled')->default(false)->after('investment_objectives');
            $table->string('two_factor_secret')->nullable()->after('two_factor_enabled');
            $table->string('withdrawal_pin')->nullable()->after('two_factor_secret');
            $table->text('backup_codes')->nullable()->after('withdrawal_pin');
            
            // Registration Progress Tracking
            $table->integer('registration_stage')->default(1)->after('backup_codes'); // 1, 2, 3, 4
            $table->boolean('registration_completed')->default(false)->after('registration_stage');
            
            // Verification Status
            $table->boolean('phone_verified')->default(false)->after('email_verified_at');
            $table->timestamp('phone_verified_at')->nullable()->after('phone_verified');
            $table->boolean('kyc_verified')->default(false)->after('phone_verified_at');
            $table->timestamp('kyc_verified_at')->nullable()->after('kyc_verified');
            $table->string('kyc_status')->default('pending')->after('kyc_verified_at'); // pending, under_review, approved, rejected
            $table->text('kyc_rejection_reason')->nullable()->after('kyc_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'full_name',
                'country_code',
                'phone',
                'country',
                'referral_code',
                'terms_accepted',
                'risk_accepted',
                'id_type',
                'id_number',
                'id_document_path',
                'selfie_path',
                'date_of_birth',
                'residential_address',
                'city',
                'state',
                'postal_code',
                'employment_status',
                'annual_income_range',
                'source_of_funds',
                'investment_experience',
                'risk_tolerance',
                'investment_objectives',
                'two_factor_enabled',
                'two_factor_secret',
                'withdrawal_pin',
                'backup_codes',
                'registration_stage',
                'registration_completed',
                'phone_verified',
                'phone_verified_at',
                'kyc_verified',
                'kyc_verified_at',
                'kyc_status',
                'kyc_rejection_reason',
            ]);
        });
    }
};