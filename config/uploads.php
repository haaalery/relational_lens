<?php
/**
 * Image Upload Helper for Relational Lens
 */

function upload_image($file, $target_dir = 'uploads/') {
    // 1. Check if file was actually uploaded
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'No file uploaded or upload error.'];
    }

    // 2. Validate File Type (Allow only images)
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $file_info = getimagesize($file['tmp_name']);
    
    if (!$file_info || !in_array($file_info['mime'], $allowed_types)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, WEBP, and GIF are allowed.'];
    }

    // 3. Validate File Size (Limit to 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        return ['success' => false, 'message' => 'File is too large. Maximum size is 5MB.'];
    }

    // 4. Generate Unique Filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = bin2hex(random_bytes(10)) . '.' . $extension;
    $target_path = $target_dir . $filename;

    // 5. Ensure directory exists
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    // 6. Move the file
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return ['success' => true, 'path' => $target_path];
    }

    return ['success' => false, 'message' => 'Failed to move uploaded file. Check folder permissions.'];
}
?>