<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

/**
 * Parses MySQL dump SQL file to extract attendees and attendee_check_ins data.
 * Used to seed Division Workshop (seminar_id 6) from u266949284_sdoattendance.sql.
 */
class AttendanceSqlParser
{
    public function __construct(
        protected string $sqlPath
    ) {}

    /**
     * Parse attendees for a given seminar_id from the SQL file.
     *
     * @return array<int, array<string, mixed>>
     */
    public function parseAttendees(int $seminarId): array
    {
        $sql = File::get($this->sqlPath);
        $attendees = [];

        // Find all INSERT INTO `attendees` blocks and extract rows for this seminar
        $pattern = '/INSERT INTO `attendees`[^V]+VALUES\s+/i';
        if (! preg_match_all($pattern, $sql, $matches, PREG_OFFSET_CAPTURE)) {
            return $attendees;
        }

        foreach ($matches[0] as [$fullMatch, $offset]) {
            $start = $offset + strlen($fullMatch);
            $block = $this->extractBlock($sql, $start);
            $rows = $this->splitRows($block);

            foreach ($rows as $rowStr) {
                $values = $this->parseRowValues($rowStr);
                if (count($values) < 2) {
                    continue;
                }
                $rowSeminarId = (int) ($values[1] ?? 0);
                if ($rowSeminarId !== $seminarId) {
                    continue;
                }

                $attendees[] = $this->mapAttendeeRow($values);
            }
        }

        return $attendees;
    }

    /**
     * Parse attendee_check_ins for given seminar_day_ids.
     * Returns map: attendee_id => [seminar_day_id => [checked_in_at, checked_out_at]]
     *
     * @param  array<int>  $seminarDayIds
     * @return array<int, array<int, array{checked_in_at: ?string, checked_out_at: ?string}>>
     */
    public function parseCheckIns(array $seminarDayIds): array
    {
        $sql = File::get($this->sqlPath);
        $checkIns = [];

        $pattern = '/INSERT INTO `attendee_check_ins`[^V]+VALUES\s+/i';
        if (! preg_match($pattern, $sql, $match, PREG_OFFSET_CAPTURE)) {
            return $checkIns;
        }

        $start = $match[0][1] + strlen($match[0][0]);
        $block = $this->extractBlock($sql, $start);
        $rows = $this->splitRows($block);

        foreach ($rows as $rowStr) {
            $values = $this->parseRowValues($rowStr);
            // id, attendee_id, seminar_day_id, checked_in_at, checked_out_at, created_at, updated_at
            if (count($values) < 5) {
                continue;
            }
            $attendeeId = (int) ($values[1] ?? 0);
            $dayId = (int) ($values[2] ?? 0);
            if (! in_array($dayId, $seminarDayIds, true)) {
                continue;
            }

            $checkIns[$attendeeId][$dayId] = [
                'checked_in_at' => $this->normalizeTimestamp($values[3] ?? null),
                'checked_out_at' => $this->normalizeTimestamp($values[4] ?? null),
            ];
        }

        return $checkIns;
    }

    /**
     * Extract content until the closing semicolon of the INSERT.
     */
    protected function extractBlock(string $sql, int $start): string
    {
        $depth = 0;
        $len = strlen($sql);
        $i = $start;

        while ($i < $len) {
            $c = $sql[$i];
            if ($c === '(') {
                $depth++;
            } elseif ($c === ')') {
                $depth--;
            } elseif ($c === ';' && $depth === 0) {
                return substr($sql, $start, $i - $start);
            }
            $i++;
        }

        return substr($sql, $start);
    }

    /**
     * Split VALUES block into individual row strings.
     */
    protected function splitRows(string $block): array
    {
        $rows = [];
        $pos = 0;
        $len = strlen($block);

        while ($pos < $len) {
            $pos = strpos($block, '(', $pos);
            if ($pos === false) {
                break;
            }
            $depth = 1;
            $start = $pos;
            $pos++;

            while ($pos < $len && $depth > 0) {
                $c = $block[$pos];
                if ($c === "'" || $c === '"') {
                    $quote = $c;
                    $pos++;
                    while ($pos < $len) {
                        if ($block[$pos] === '\\' && $pos + 1 < $len) {
                            $pos += 2;
                            continue;
                        }
                        if ($block[$pos] === $quote) {
                            $pos++;
                            break;
                        }
                        $pos++;
                    }
                    continue;
                }
                if ($c === '(') {
                    $depth++;
                } elseif ($c === ')') {
                    $depth--;
                    if ($depth === 0) {
                        $rows[] = substr($block, $start + 1, $pos - $start - 2);
                        $pos++;
                        break;
                    }
                }
                $pos++;
            }
        }

        return $rows;
    }

    /**
     * Parse comma-separated values from a row string.
     *
     * @return array<int, string|null>
     */
    protected function parseRowValues(string $row): array
    {
        $values = [];
        $len = strlen($row);
        $i = 0;

        while ($i < $len) {
            while ($i < $len && ($row[$i] === ' ' || $row[$i] === "\t" || $row[$i] === "\n")) {
                $i++;
            }
            if ($i >= $len) {
                break;
            }

            if (strtoupper(substr($row, $i, 4)) === 'NULL' && ($i + 4 >= $len || in_array($row[$i + 4], [',', ')', ' ', "\t"], true))) {
                $values[] = null;
                $i += 4;
                continue;
            }

            if ($row[$i] === "'") {
                $i++;
                $val = '';
                while ($i < $len) {
                    if ($row[$i] === '\\' && $i + 1 < $len) {
                        $i++;
                        $val .= $row[$i];
                        $i++;
                        continue;
                    }
                    if ($row[$i] === "'") {
                        $i++;
                        break;
                    }
                    $val .= $row[$i];
                    $i++;
                }
                $values[] = $val;
                continue;
            }

            if ($row[$i] === '-' || $row[$i] === '.' || ctype_digit($row[$i])) {
                $start = $i;
                if ($row[$i] === '-') {
                    $i++;
                }
                while ($i < $len && (ctype_digit($row[$i]) || $row[$i] === '.')) {
                    $i++;
                }
                $values[] = substr($row, $start, $i - $start);
                continue;
            }

            $i++;
        }

        return $values;
    }

    protected function normalizeTimestamp(?string $v): ?string
    {
        if ($v === null || $v === '') {
            return null;
        }

        return trim($v, "'\"");
    }

    /**
     * Map parsed attendee row to our structure.
     * Columns: id, seminar_id, name, email, personnel_type, first_name, middle_name, last_name,
     * suffix, sex, school_office_agency, mobile_phone, prc_license_no, prc_license_expiry,
     * signature_consent, signature_image, signature_upload_path, signature_timestamp,
     * signature_hash, signature_metadata, position, ticket_hash, checked_in_at, checked_out_at,
     * created_at, updated_at
     *
     * @param  array<int, string|null>  $values
     * @return array<string, mixed>
     */
    protected function mapAttendeeRow(array $values): array
    {
        $get = fn (int $i) => isset($values[$i]) && $values[$i] !== null ? trim((string) $values[$i], "'\"") : null;

        return [
            'sql_id' => (int) ($values[0] ?? 0),
            'name' => $get(2),
            'email' => $get(3),
            'personnel_type' => $get(4) ?: 'teaching',
            'first_name' => $get(5),
            'middle_name' => $get(6),
            'last_name' => $get(7),
            'suffix' => $get(8),
            'sex' => $get(9) ?: 'male',
            'school_office_agency' => $get(10),
            'mobile_phone' => $get(11),
            'prc_license_no' => $get(12),
            'prc_license_expiry' => $get(13),
            'signature_consent' => (bool) ($values[14] ?? 0),
            'signature_image' => $get(15),
            'signature_upload_path' => $get(16),
            'signature_timestamp' => $get(17),
            'signature_hash' => $get(18),
            'signature_metadata' => $get(19) ? json_decode($get(19), true) : null,
            'position' => $get(20) ?: '',
            'ticket_hash' => $get(21),
            'checked_in_at' => $this->normalizeTimestamp($values[22] ?? null),
            'checked_out_at' => $this->normalizeTimestamp($values[23] ?? null),
        ];
    }
}
