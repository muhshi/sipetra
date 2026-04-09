<?php
$user = \App\Models\User::where('email', 'admin@dummyapp.test')->first();
if ($user) {
    $user->update(['is_active' => true]);
    $user->assignRole('super_admin');
    echo "User updated successfully";
} else {
    echo "User not found";
}
