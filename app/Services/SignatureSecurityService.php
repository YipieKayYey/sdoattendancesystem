<?php

namespace App\Services;

use App\Models\Attendee;
use App\Models\Seminar;

class SignatureSecurityService
{

    /**
     * Apply security watermark to signature image
     */
    public function applyWatermark(string $signatureImage, Attendee $attendee, Seminar $seminar): string
    {
        // Decode base64 image if needed
        if (str_starts_with($signatureImage, 'data:image')) {
            $signatureImage = $this->extractBase64Image($signatureImage);
        }

        $imageData = base64_decode($signatureImage);
        $sourceImage = imagecreatefromstring($imageData);
        
        if (!$sourceImage) {
            throw new \Exception('Invalid image data');
        }

        $width = imagesx($sourceImage);
        $height = imagesy($sourceImage);

        // Create watermark color (semi-transparent red)
        $watermarkColor = imagecolorallocatealpha($sourceImage, 200, 0, 0, 12); // 12 = ~90% opacity
        $lightWatermarkColor = imagecolorallocatealpha($sourceImage, 200, 0, 0, 12); // 12 = ~90% opacity

        // Watermark text components
        $watermarkTexts = [
            "SDO SEMINAR SYSTEM - SIGNATURE",
            "Valid for: " . substr($seminar->title, 0, 40),
            "Date: " . now()->format('Y-m-d H:i:s') . " | ID: " . $attendee->id,
        ];

        // Add top watermarks
        $fontSize = 2; // GD built-in font size (1-5)
        $yOffset = 10;
        foreach ($watermarkTexts as $text) {
            imagestring($sourceImage, $fontSize, 5, $yOffset, $text, $watermarkColor);
            $yOffset += 15;
        }

        // Add diagonal watermark overlay (multiple positions)
        $diagonalText = "SDO SEMINAR SYSTEM";
        for ($i = 0; $i < 3; $i++) {
            $x = ($width / 4) * ($i + 1);
            $y = ($height / 4) * ($i + 1);
            imagestring($sourceImage, 3, $x, $y, $diagonalText, $lightWatermarkColor);
        }

        // Add bottom metadata
        $metadataText = "Seminar: {$seminar->id} | Attendee: {$attendee->id}";
        imagestring($sourceImage, 1, 5, $height - 15, $metadataText, $watermarkColor);

        // Convert to base64
        ob_start();
        imagepng($sourceImage);
        $imageData = ob_get_contents();
        ob_end_clean();
        imagedestroy($sourceImage);

        return base64_encode($imageData);
    }

    /**
     * Generate security hash for signature
     */
    public function generateSignatureHash(string $signatureImage, array $metadata): string
    {
        $data = $signatureImage . json_encode($metadata) . config('app.key');
        return hash('sha256', $data);
    }

    /**
     * Validate signature hash
     */
    public function validateSignatureHash(Attendee $attendee): bool
    {
        if (empty($attendee->signature_hash) || empty($attendee->signature_image)) {
            return false;
        }

        $expectedHash = $this->generateSignatureHash(
            $attendee->signature_image,
            $attendee->signature_metadata ?? []
        );

        return hash_equals($attendee->signature_hash, $expectedHash);
    }

    /**
     * Embed metadata in signature image
     */
    public function embedMetadata(string $signatureImage, array $metadata): string
    {
        // Metadata is already embedded in applyWatermark, so just return as-is
        // This method is kept for API consistency
        return $signatureImage;
    }

    /**
     * Process and store signature
     */
    public function processSignature(string $signatureData, Attendee $attendee, Seminar $seminar): array
    {
        // Extract base64 if needed
        if (str_starts_with($signatureData, 'data:image')) {
            $signatureData = $this->extractBase64Image($signatureData);
        }

        // Prepare metadata
        $metadata = [
            'seminar_id' => $seminar->id,
            'attendee_id' => $attendee->id,
            'form_type' => 'registration',
            'timestamp' => now()->toIso8601String(),
            'ip_address' => request()->ip(),
        ];

        // Apply watermark
        $watermarkedImage = $this->applyWatermark($signatureData, $attendee, $seminar);

        // Embed metadata
        $finalImage = $this->embedMetadata($watermarkedImage, $metadata);

        // Generate hash
        $hash = $this->generateSignatureHash($finalImage, $metadata);

        return [
            'signature_image' => $finalImage,
            'signature_hash' => $hash,
            'signature_metadata' => $metadata,
            'signature_timestamp' => now(),
        ];
    }

    /**
     * Extract base64 image data from data URI
     */
    protected function extractBase64Image(string $dataUri): string
    {
        if (preg_match('/data:image\/(\w+);base64,(.+)/', $dataUri, $matches)) {
            return $matches[2];
        }
        return $dataUri;
    }

    /**
     * Store signature file to disk
     */
    public function storeSignatureFile(string $signatureImage, Attendee $attendee, Seminar $seminar): string
    {
        // Extract base64 if needed
        if (str_starts_with($signatureImage, 'data:image')) {
            $signatureImage = $this->extractBase64Image($signatureImage);
        }

        $filename = sprintf(
            '%d_%d_%s.png',
            $attendee->id,
            $seminar->id,
            now()->format('YmdHis')
        );

        $path = storage_path('app/signatures/' . $filename);
        
        // Ensure directory exists
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, base64_decode($signatureImage));

        return 'signatures/' . $filename;
    }
}
