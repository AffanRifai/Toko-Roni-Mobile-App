<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Face Recognition Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk sistem face recognition
    |
    */

    // Threshold untuk matching wajah (0.0 - 1.0)
    // Semakin kecil nilai, semakin ketat matchingnya
    'threshold' => env('FACE_RECOGNITION_THRESHOLD', 0.6),

    // Path untuk menyimpan model FaceAPI
    'models_path' => public_path('models'),

    // Validasi panjang descriptor (FaceAPI menghasilkan 128 nilai)
    'descriptor_length' => 128,

    // Minimum score untuk registrasi wajah
    'min_registration_score' => 0.7,

    // Simpan gambar wajah saat registrasi
    'save_face_image' => env('SAVE_FACE_IMAGE', true),

    // Path untuk menyimpan gambar wajah
    'face_images_path' => 'faces',

    // Format gambar yang disimpan
    'image_format' => 'jpg',

    // Kualitas gambar (0-100)
    'image_quality' => 80,
];
