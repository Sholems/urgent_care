<?php

return [
  'credentials' => env('FIREBASE_CREDENTIALS', base_path('firebase-admin.json')),
  'messaging_scope' => 'https://www.googleapis.com/auth/firebase.messaging',
];
