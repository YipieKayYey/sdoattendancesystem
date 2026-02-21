<?php

namespace App\Services;

use App\Models\Seminar;
use mikehaertl\pdftk\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

/**
 * PDF Form Filling Service
 * Fills fillable PDF forms dynamically based on number of attendees (pdftk).
 *
 * Optional alternative: not wired to any route or Filament action by default.
 * The app uses RegistrationSheetPdfService (DomPDF) for registration sheet export.
 * Wire this service if you need fillable-form output instead.
 */
class PdfFormFillingService
{
    protected string $templatePath;

    public function __construct()
    {
        // Use the user's new fillable template generated via PDFEscape
        $this->templatePath = public_path('pdf/PDD-12-A-Registration-Sheet-form-Fillable1.pdf');
    }

    /**
     * Generate Registration Sheet by filling PDF form fields
     */
    public function generateRegistrationSheet(Seminar $seminar): Response
    {
        $attendees = $seminar->attendees()->orderBy('created_at')->get();

        // Ensure tmp directory exists
        $outputDir = storage_path('app/tmp');
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0775, true);
        }

        $filename = sprintf(
            'Registration-Sheet-%s-%s.pdf',
            Str::slug($seminar->title),
            now()->format('Y-m-d')
        );
        $outputPath = $outputDir . '/' . $filename;

        // If there are no attendees yet, just export a blank copy of the template
        if ($attendees->isEmpty()) {
            $pdf = new Pdf($this->templatePath);
            if (!$pdf->saveAs($outputPath)) {
                abort(500, 'PDF form filling failed (blank template): ' . $pdf->getError());
            }

            return response()->download($outputPath, $filename)->deleteFileAfterSend(true);
        }

        // Chunk attendees into pages of 8 rows each
        $chunks = $attendees->chunk(8);

        $tempFiles = [];
        $globalOffset = 0; // how many attendees processed so far

        foreach ($chunks as $pageIndex => $pageAttendees) {
            // Build form data for this page only
            $formData = $this->buildFormData($seminar, $pageAttendees, $globalOffset);

            // Fill PDF form for this page
            $pdf = new Pdf($this->templatePath);
            $pdf->fillForm($formData)
                ->needAppearances()
                ->flatten();

            $pageFilename = sprintf(
                'registration-page-%s-%d-%s.pdf',
                Str::slug($seminar->title),
                $pageIndex + 1,
                Str::random(6)
            );
            $pagePath = storage_path('app/tmp/' . $pageFilename);
            $outputDir = dirname($pagePath);
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0775, true);
            }
            if (!$pdf->saveAs($pagePath)) {
                abort(500, 'PDF form filling failed (single page): ' . $pdf->getError());
            }
            $tempFiles[] = $pagePath;

            $globalOffset += $pageAttendees->count();
        }

        // Merge all pages into a single PDF (with data)

        if (count($tempFiles) === 1) {
            // Single page - just rename/move
            rename($tempFiles[0], $outputPath);
        } else {
            $merged = new Pdf($tempFiles);
            if (!$merged->cat()->saveAs($outputPath)) {
                abort(500, 'PDF form filling failed (merge): ' . $merged->getError());
            }

            // Clean up individual page files
            foreach ($tempFiles as $file) {
                @unlink($file);
            }
        }

        return response()->download($outputPath, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Build form data dynamically based on number of attendees
     * Only fills fields that have data - unused rows remain blank
     */
    protected function buildFormData(Seminar $seminar, $attendees, int $offset = 0): array
    {
        $formData = [];

        // Header fields (same on all pages)
        $formData['title'] = $seminar->title;
        $formData['date'] = $seminar->date->format('F j, Y');
        $formData['venue'] = $seminar->venue ?? 'N/A';

        // Fill attendee rows dynamically for THIS page
        // Field names follow the convention from PDFESCAPE_QUICK_START.md:
        // no_1, name_1, signature_1, mobile_1, email_1, prc_1, expiry_1, etc.
        foreach ($attendees as $index => $attendee) {
            $fieldRow = $index + 1;          // 1..8 (position on this page)
            $displayNumber = $offset + $index + 1; // global 1,2,3,4,... (not based on ID)

            // Fill all fields for this row (local row indices)
            $formData["no_{$fieldRow}"] = (string)$displayNumber;
            
            $formData["name_{$fieldRow}"] = $attendee->full_name ?: $attendee->name;

            $formData["mobile_{$fieldRow}"] = $attendee->mobile_phone ?? '';
            $formData["email_{$fieldRow}"] = $attendee->email;
            
            // PRC fields (only for teaching personnel)
            if ($attendee->isTeaching()) {
                $formData["prc_{$fieldRow}"] = $attendee->prc_license_no ?? 'N/A';
                $formData["expiry_{$fieldRow}"] = $attendee->prc_license_expiry 
                    ? $attendee->prc_license_expiry->format('d/m/Y')
                    : 'N/A';
            } else {
                $formData["prc_{$fieldRow}"] = 'N/A';
                $formData["expiry_{$fieldRow}"] = 'N/A';
            }

            // Handle signature
            if ($attendee->hasSignature() && $attendee->signature_image) {
                // For signature, we might need to embed as image
                // This depends on how the PDF form field is set up
                $formData["signature_{$fieldRow}"] = $this->prepareSignature($attendee->signature_image);
            } else {
                $formData["signature_{$fieldRow}"] = '';
            }
        }

        // Note: Fields for rows beyond the attendee count (e.g., row13-80 if only 12 attendees)
        // will remain empty/blank in the PDF - this is expected behavior

        return $formData;
    }

    /**
     * Prepare signature for PDF embedding
     * This depends on how the PDF form field accepts signatures
     */
    protected function prepareSignature(string $signatureBase64): string
    {
        // Option 1: If signature field is text, convert to text representation
        // Option 2: If signature field accepts images, embed the image
        // Option 3: Use a checkmark or placeholder
        
        // For now, return a placeholder - we'll adjust based on PDF form field type
        return '✓'; // Or embed image if field supports it
    }
}
