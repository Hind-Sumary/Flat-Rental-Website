<?php
// Birzeit Flat Rent - Reusable PHP Functions

// Function to sanitize output - UPDATED to handle null values
function esc($value): string {
    // Handle null values by converting to empty string
    if ($value === null) {
        return '';
    }
    // Convert to string if not already
    $value = (string) $value;
    // Use htmlspecialchars for preventing XSS
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Function to redirect user
function redirect(string $url): void {
    header("Location: " . $url);
    exit;
}

function getSortIcon(string $column_name, string $current_sort_column, string $current_sort_order): string {
    if ($column_name === $current_sort_column) {
        return $current_sort_order === 'ASC' ? ' &#9650;' : ' &#9660;'; // ▲ or ▼
    }
    return '';
}

// Helper function to safely get array value
function safe_get($array, $key, $default = '') {
    return isset($array[$key]) ? $array[$key] : $default;
}

// Helper function to safely escape array value
function safe_esc($array, $key, $default = '') {
    return esc(safe_get($array, $key, $default));
}

// === PHOTO FUNCTIONS FOR YOUR FILE STRUCTURE (images/F001/F001_1.jpg) ===

// Get photo directory for a flat (YOUR structure: images/F001/)
function getFlatPhotoDir($flat_ref_no) {
    return "images/$flat_ref_no/";
}

// Get specific photo path (YOUR structure: images/F001/F001_1.jpg)
function getFlatPhotoPath($flat_ref_no, $photo_number) {
    return "images/$flat_ref_no/{$flat_ref_no}_$photo_number.jpg";
}

// Get main photo (YOUR structure: images/F001/F001_1.jpg)
function getFlatMainPhoto($flat_ref_no) {
    return getFlatPhotoPath($flat_ref_no, 1);
}

// Get all available photos for a flat (checks which files actually exist)
function getAvailableFlatPhotos($flat_ref_no, $max_photos = 10) {
    $photos = [];
    for ($i = 1; $i <= $max_photos; $i++) {
        $path = getFlatPhotoPath($flat_ref_no, $i);
        if (file_exists($path)) {
            $photos[] = [
                'number' => $i,
                'path' => $path,
                'is_main' => ($i === 1),
                'filename' => "{$flat_ref_no}_$i.jpg"
            ];
        }
    }
    return $photos;
}

// Check if flat has any photos
function flatHasPhotos($flat_ref_no) {
    $main_photo = getFlatMainPhoto($flat_ref_no);
    return file_exists($main_photo);
}

// Get photo count for a flat
function getFlatPhotoCount($flat_ref_no, $max_check = 10) {
    $count = 0;
    for ($i = 1; $i <= $max_check; $i++) {
        $path = getFlatPhotoPath($flat_ref_no, $i);
        if (file_exists($path)) {
            $count++;
        }
    }
    return $count;
}