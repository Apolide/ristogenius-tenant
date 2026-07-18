<?php

return [
    'title' => 'Personnel', 'add' => 'Add employee', 'edit' => 'Edit employee',
    'new_user' => 'NEW USER', 'edit_employee' => 'EDIT EMPLOYEE',
    'name' => 'Full name', 'phone' => 'Phone', 'role' => 'Platform role',
    'select_role' => 'Select role', 'language' => 'Language',
    'whatsapp' => 'Can receive WhatsApp notifications', 'telegram' => 'Can receive Telegram notifications',
    'save_invite' => 'Save and send invitation', 'sending' => 'Sending…',
    'save' => 'Save changes', 'saving' => 'Saving…', 'cancel' => 'Cancel',
    'refresh' => 'Refresh', 'yes' => 'YES', 'no' => 'NO',
    'index' => [
        'search' => 'Search users', 'actions' => 'Actions', 'name' => 'Name', 'status' => 'Status',
        'role' => 'Role', 'whatsapp_notifications' => 'WhatsApp notifications',
        'telegram_notifications' => 'Telegram notifications', 'active' => 'Active', 'invited' => 'Invited',
        'delete' => 'Delete', 'delete_confirmation' => 'Are you sure you want to delete this user?',
        'empty' => 'No users found.',
    ],
    'permissions' => [
        'title' => 'Personnel permissions', 'search' => 'Search employees', 'employee' => 'Employee',
        'empty' => 'No employees found.', 'aria' => ':permission for :name',
    ],
    'roles' => ['admin' => 'Administrator', 'manager' => 'Manager', 'operator' => 'Operator', 'kiosk' => 'Kiosk'],
    'features' => [
        'ai' => 'AI management', 'calendar' => 'Calendar management', 'cash_register' => 'Cash register management',
        'customers' => 'Customer management', 'orders' => 'Order management', 'marketing' => 'Marketing management',
        'marketplace' => 'Marketplace management', 'bookings' => 'Booking management',
        'products' => 'Product management', 'rooms' => 'Room/Table management', 'shifts' => 'Shift/Timesheet management',
    ],
    'validation' => ['phone' => 'Enter the phone number in international format, for example +393401234567.'],
    'messages' => [
        'created' => 'Employee created. The activation email has been sent.', 'updated' => 'Employee updated.',
        'deleted' => 'User deleted.', 'cannot_delete_self' => 'You cannot delete your own account.',
        'permission_updated' => 'Permission updated.',
    ],
];
