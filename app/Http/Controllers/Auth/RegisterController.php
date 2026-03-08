<?php
// app/Http/Controllers/Auth/RegisterController.php
// REPLACE YOUR EXISTING RegisterController WITH THIS

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    /**
     * ===================================================================
     * STAGE 1: BASIC ACCOUNT REGISTRATION
     * ===================================================================
     */
    
    public function showStage1()
    {
        return view('auth.register-stage1');
    }

    public function submitStage1(Request $request)
    {
        // Validate Stage 1 inputs
        $validated = $request->validate([
            'full_name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'email' => 'required|email|unique:users,email|max:255',
            'country_code' => 'required|string|max:10',
            'phone' => 'required|string|max:20',
            'country' => 'required|string|size:2',
            'password' => 'required|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            'referral_code' => 'nullable|string|max:20',
            'terms_accepted' => 'required|accepted',
            'risk_accepted' => 'required|accepted',
        ], [
            'full_name.regex' => 'Full name should contain only letters and spaces.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number.',
            'terms_accepted.accepted' => 'You must accept the Terms of Service.',
            'risk_accepted.accepted' => 'You must accept the Investment Risk Disclosure.',
        ]);

        // Create the user
        $user = User::create([
            'name' => $validated['full_name'], // Keep for compatibility
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'country_code' => $validated['country_code'],
            'phone' => $validated['phone'],
            'country' => $validated['country'],
            'password' => Hash::make($validated['password']),
            'referral_code' => $validated['referral_code'],
            'terms_accepted' => true,
            'risk_accepted' => true,
            'registration_stage' => 1,
            'registration_completed' => false,
        ]);

        // Store user ID in session for multi-step process
        session(['registration_user_id' => $user->id]);

        // TODO: Send email verification
        // $this->sendVerificationEmail($user);

        return redirect()->route('register.stage2')
            ->with('success', 'Account created successfully! Please complete KYC verification.');
    }

    /**
     * ===================================================================
     * STAGE 2: KYC VERIFICATION
     * ===================================================================
     */
    
    public function showStage2()
    {
        $userId = session('registration_user_id');
        if (!$userId) {
            return redirect()->route('register')
                ->with('error', 'Please start registration from the beginning.');
        }
        
        $user = User::find($userId);
        return view('auth.register-stage2', compact('user'));
    }

    public function submitStage2(Request $request)
    {
        $userId = session('registration_user_id');
        if (!$userId) {
            return redirect()->route('register');
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('register')
                ->with('error', 'User not found.');
        }

        // Validate Stage 2 inputs
        $validated = $request->validate([
            'id_type' => 'required|in:passport,national_id,drivers_license',
            'id_number' => 'required|string|max:50',
            'id_document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB max
            'selfie' => 'required|file|mimes:jpg,jpeg,png|max:5120',
            'date_of_birth' => 'required|date|before:-18 years',
            'residential_address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
        ], [
            'date_of_birth.before' => 'You must be at least 18 years old to register.',
            'id_document.max' => 'ID document must not exceed 5MB.',
            'selfie.max' => 'Selfie must not exceed 5MB.',
        ]);

        // Store uploaded files
        $idDocumentPath = $request->file('id_document')->store('kyc/documents/' . $user->id, 'public');
        $selfiePath = $request->file('selfie')->store('kyc/selfies/' . $user->id, 'public');

        // Update user with KYC information
        $user->update([
            'id_type' => $validated['id_type'],
            'id_number' => $validated['id_number'],
            'id_document_path' => $idDocumentPath,
            'selfie_path' => $selfiePath,
            'date_of_birth' => $validated['date_of_birth'],
            'residential_address' => $validated['residential_address'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'postal_code' => $validated['postal_code'],
            'registration_stage' => 2,
            'kyc_status' => 'under_review',
        ]);

        return redirect()->route('register.stage3')
            ->with('success', 'KYC documents uploaded successfully! Please complete your investor profile.');
    }

    /**
     * ===================================================================
     * STAGE 3: INVESTOR SUITABILITY PROFILE
     * ===================================================================
     */
    
    public function showStage3()
    {
        $userId = session('registration_user_id');
        if (!$userId) {
            return redirect()->route('register');
        }
        
        $user = User::find($userId);
        return view('auth.register-stage3', compact('user'));
    }

    public function submitStage3(Request $request)
    {
        $userId = session('registration_user_id');
        if (!$userId) {
            return redirect()->route('register');
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('register');
        }

        // Validate Stage 3 inputs
        $validated = $request->validate([
            'employment_status' => 'required|in:employed,self_employed,unemployed,retired,student',
            'annual_income_range' => 'required|in:below_25k,25k_50k,50k_100k,100k_250k,above_250k',
            'source_of_funds' => 'required|in:salary,business_income,investments,inheritance,savings,other',
            'investment_experience' => 'required|in:none,beginner,intermediate,experienced,expert',
            'risk_tolerance' => 'required|in:conservative,moderate,aggressive',
            'investment_objectives' => 'required|string|max:1000',
        ]);

        // Update user with investor profile
        $user->update([
            'employment_status' => $validated['employment_status'],
            'annual_income_range' => $validated['annual_income_range'],
            'source_of_funds' => $validated['source_of_funds'],
            'investment_experience' => $validated['investment_experience'],
            'risk_tolerance' => $validated['risk_tolerance'],
            'investment_objectives' => $validated['investment_objectives'],
            'registration_stage' => 3,
        ]);

        return redirect()->route('register.stage4')
            ->with('success', 'Investor profile completed! One more step to secure your account.');
    }

    /**
     * ===================================================================
     * STAGE 4: SECURITY SETUP
     * ===================================================================
     */
    
    public function showStage4()
    {
        $userId = session('registration_user_id');
        if (!$userId) {
            return redirect()->route('register');
        }
        
        $user = User::find($userId);
        return view('auth.register-stage4', compact('user'));
    }

    public function submitStage4(Request $request)
    {
        $userId = session('registration_user_id');
        if (!$userId) {
            return redirect()->route('register');
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('register');
        }

        // Validate Stage 4 inputs
        $validated = $request->validate([
            'withdrawal_pin' => 'required|digits:4|confirmed',
            'enable_2fa' => 'nullable|boolean',
        ], [
            'withdrawal_pin.digits' => 'Withdrawal PIN must be exactly 4 digits.',
            'withdrawal_pin.confirmed' => 'PIN confirmation does not match.',
        ]);

        // Update user with security settings
        $user->update([
            'withdrawal_pin' => Hash::make($validated['withdrawal_pin']),
            'two_factor_enabled' => $request->boolean('enable_2fa'),
            'registration_stage' => 4,
            'registration_completed' => true,
        ]);

        // If 2FA is enabled, generate secret (implement this later)
        if ($request->boolean('enable_2fa')) {
            // TODO: Generate 2FA secret using google2fa package
            // $user->update(['two_factor_secret' => ...]);
        }

        // Clear session data
        session()->forget('registration_user_id');

        // Log the user in
        Auth::login($user);

        return redirect()->route('investor.dashboard')
            ->with('success', 'Registration completed successfully! Welcome to Smart System Investment.');
    }

    /**
     * ===================================================================
     * EMAIL VERIFICATION (Optional - Implement Later)
     * ===================================================================
     */
    
    public function verifyEmail($token)
    {
        $user = User::where('remember_token', $token)->first();

        if (!$user) {
            return redirect()->route('register')
                ->with('error', 'Invalid verification link.');
        }

        $user->update([
            'email_verified_at' => now(),
        ]);

        return redirect()->route('register.stage2')
            ->with('success', 'Email verified successfully!');
    }

    public function resendVerification(Request $request)
    {
        $userId = session('registration_user_id');
        if (!$userId) {
            return redirect()->route('register');
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('register');
        }

        // TODO: Resend verification email
        // $this->sendVerificationEmail($user);

        return back()->with('success', 'Verification email resent!');
    }

    /**
     * ===================================================================
     * HELPER METHODS
     * ===================================================================
     */
    
    private function sendVerificationEmail($user)
    {
        // TODO: Implement email sending logic
        // You can use Laravel Mail or a service like SendGrid, Mailgun, etc.
        
        $token = Str::random(60);
        $user->update(['remember_token' => $token]);

        // Mail::to($user->email)->send(new VerificationEmail($user, $token));
    }
}