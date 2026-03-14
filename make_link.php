<?php
$target = '/home/esamnkiu/public_html';
$shortcut = '/home/esamnkiu/esampatha.com/public';

if (symlink($target, $shortcut)) {
    echo "Symlink created successfully!";
} else {
    echo "Failed to create symlink. Check if 'public' folder already exists.";
}
?>