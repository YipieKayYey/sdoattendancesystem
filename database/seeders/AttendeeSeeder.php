<?php

namespace Database\Seeders;

use App\Models\Attendee;
use App\Models\Seminar;
use App\Services\SignatureSecurityService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AttendeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $seminars = Seminar::all();
        
        if ($seminars->isEmpty()) {
            $this->command->warn('No seminars found. Please run SeminarSeeder first.');
            return;
        }

        $securityService = app(SignatureSecurityService::class);

        // Sample teaching personnel data
        $teachingAttendees = [
            [
                'first_name' => 'Maria',
                'middle_name' => 'Santos',
                'last_name' => 'Cruz',
                'suffix' => null,
                'email' => 'maria.cruz@example.com',
                'mobile_phone' => '09171234567',
                'position' => 'Master Teacher I',
                'personnel_type' => 'teaching',
                'prc_license_no' => '123456',
                'prc_license_expiry' => now()->addYears(2),
                'checked_in' => true,
            ],
            [
                'first_name' => 'Juan',
                'middle_name' => 'Dela',
                'last_name' => 'Cruz',
                'suffix' => 'Jr.',
                'email' => 'juan.delacruz@example.com',
                'mobile_phone' => '09172345678',
                'position' => 'Teacher III',
                'personnel_type' => 'teaching',
                'prc_license_no' => '234567',
                'prc_license_expiry' => now()->addYears(1),
                'checked_in' => true,
            ],
            [
                'first_name' => 'Ana',
                'middle_name' => 'Reyes',
                'last_name' => 'Garcia',
                'suffix' => null,
                'email' => 'ana.garcia@example.com',
                'mobile_phone' => '09173456789',
                'position' => 'Head Teacher',
                'personnel_type' => 'teaching',
                'prc_license_no' => '345678',
                'prc_license_expiry' => now()->addMonths(18),
                'checked_in' => false,
            ],
            [
                'first_name' => 'Carlos',
                'middle_name' => 'Villanueva',
                'last_name' => 'Ramos',
                'email' => 'carlos.ramos@example.com',
                'mobile_phone' => '09174567890',
                'position' => 'Teacher II',
                'personnel_type' => 'teaching',
                'prc_license_no' => '456789',
                'prc_license_expiry' => now()->addYears(3),
                'checked_in' => true,
            ],
            [
                'first_name' => 'Liza',
                'middle_name' => 'Fernandez',
                'last_name' => 'Torres',
                'email' => 'liza.torres@example.com',
                'mobile_phone' => '09175678901',
                'position' => 'Master Teacher II',
                'personnel_type' => 'teaching',
                'prc_license_no' => '567890',
                'prc_license_expiry' => now()->addYears(2),
                'checked_in' => false,
            ],
        ];

        // Sample non-teaching personnel data
        $nonTeachingAttendees = [
            [
                'first_name' => 'Roberto',
                'middle_name' => 'Mendoza',
                'last_name' => 'Lopez',
                'suffix' => 'III',
                'email' => 'roberto.lopez@example.com',
                'mobile_phone' => '09176789012',
                'position' => 'Administrative Officer',
                'personnel_type' => 'non_teaching',
                'prc_license_no' => null,
                'prc_license_expiry' => null,
                'checked_in' => true,
            ],
            [
                'first_name' => 'Patricia',
                'middle_name' => 'Alvarez',
                'last_name' => 'Martinez',
                'email' => 'patricia.martinez@example.com',
                'mobile_phone' => '09177890123',
                'position' => 'Guidance Counselor',
                'personnel_type' => 'non_teaching',
                'prc_license_no' => null,
                'prc_license_expiry' => null,
                'checked_in' => true,
            ],
            [
                'first_name' => 'Michael',
                'middle_name' => 'Gonzales',
                'last_name' => 'Rivera',
                'email' => 'michael.rivera@example.com',
                'mobile_phone' => '09178901234',
                'position' => 'IT Support Staff',
                'personnel_type' => 'non_teaching',
                'prc_license_no' => null,
                'prc_license_expiry' => null,
                'checked_in' => false,
            ],
            [
                'first_name' => 'Jennifer',
                'middle_name' => 'Bautista',
                'last_name' => 'Sanchez',
                'email' => 'jennifer.sanchez@example.com',
                'mobile_phone' => '09179012345',
                'position' => 'Librarian',
                'personnel_type' => 'non_teaching',
                'prc_license_no' => null,
                'prc_license_expiry' => null,
                'checked_in' => true,
            ],
        ];

        $allAttendees = array_merge($teachingAttendees, $nonTeachingAttendees);

        // Create attendees for each seminar
        foreach ($seminars as $seminar) {
            foreach ($allAttendees as $index => $attendeeData) {
                // Generate unique ticket hash
                do {
                    $ticketHash = Str::random(16);
                } while (Attendee::where('ticket_hash', $ticketHash)->exists());

                // Create a simple signature image (base64 encoded PNG)
                $signatureImage = $this->generateSampleSignature($attendeeData['first_name'] . ' ' . $attendeeData['last_name']);

                // Create attendee
                $attendee = Attendee::create([
                    'seminar_id' => $seminar->id,
                    'name' => trim(
                        $attendeeData['first_name'] . ' ' .
                        ($attendeeData['middle_name'] ?? '') . ' ' .
                        $attendeeData['last_name'] .
                        (!empty($attendeeData['suffix']) ? ', ' . $attendeeData['suffix'] : '')
                    ),
                    'email' => $attendeeData['email'],
                    'position' => $attendeeData['position'],
                    'ticket_hash' => $ticketHash,
                    'personnel_type' => $attendeeData['personnel_type'],
                    'first_name' => $attendeeData['first_name'],
                    'middle_name' => $attendeeData['middle_name'],
                    'last_name' => $attendeeData['last_name'],
                    'suffix' => $attendeeData['suffix'] ?? null,
                    'mobile_phone' => $attendeeData['mobile_phone'],
                    'prc_license_no' => $attendeeData['prc_license_no'],
                    'prc_license_expiry' => $attendeeData['prc_license_expiry'],
                    'signature_consent' => true,
                    'signature_image' => null, // Will be set after processing
                    'checked_in_at' => $attendeeData['checked_in'] ? now()->subHours(rand(1, 24)) : null,
                ]);

                // Process and secure signature
                if ($signatureImage) {
                    $processed = $securityService->processSignature($signatureImage, $attendee, $seminar);
                    $processed['signature_upload_path'] = $securityService->storeSignatureFile($processed['signature_image'], $attendee, $seminar);
                    $attendee->update($processed);
                }
            }
        }

        $this->command->info('Created ' . count($allAttendees) . ' attendees for each seminar.');
    }

    /**
     * Generate a simple sample signature image
     */
    protected function generateSampleSignature(string $name): string
    {
        // Create a simple signature image using GD
        $width = 300;
        $height = 100;
        $image = imagecreatetruecolor($width, $height);
        
        // White background
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $white);
        
        // Black text for signature
        $black = imagecolorallocate($image, 0, 0, 0);
        
        // Draw signature text (simplified)
        $fontSize = 5; // GD built-in font
        $text = substr($name, 0, 20); // Limit text length
        imagestring($image, $fontSize, 10, 40, $text, $black);
        
        // Add a simple line (signature line)
        imageline($image, 10, 70, 290, 70, $black);
        
        // Convert to base64
        ob_start();
        imagepng($image);
        $imageData = ob_get_contents();
        ob_end_clean();
        imagedestroy($image);
        
        return base64_encode($imageData);
    }
}
